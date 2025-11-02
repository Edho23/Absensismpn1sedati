<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Absensi, Siswa, KartuRfid};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    /**
     * POST /api/attendances/hit
     * Body JSON: { uid: "04:A1:1C:L1", kode_perangkat: "GATE-1" (ops), waktu: ISO8601 (ops) }
     * Catatan:
     * - Server akan cari NIS via uid (tabel kartu_rfid).
     * - Menentukan MASUK / PULANG dari kondisi data & jadwal.
     * - Menolak double-tap: jika jam_masuk sudah ada -> tidak diisi lagi; jika jam_pulang sudah ada -> tolak.
     */
    public function hit(Request $r)
    {
        // --------- Validasi awal ---------
        $data = $r->validate([
            'uid'            => 'required|string|max:100',
            'kode_perangkat' => 'nullable|string|max:100',
            'waktu'          => 'nullable|string', // ISO8601; kalau kosong pakai server time
        ]);

        // WIB sekarang (atau dari payload)
        $nowWib = $data['waktu'] 
            ? Carbon::parse($data['waktu'])->setTimezone('Asia/Jakarta')
            : Carbon::now('Asia/Jakarta');

        $tanggal = $nowWib->toDateString();
        $waktuHms = $nowWib->format('H:i:s');

        // --------- Map UID → NIS ---------
        $kartu = KartuRfid::where('uid', $data['uid'])->first();
        if (!$kartu) {
            return response()->json([
                'ok' => false,
                'state' => 'NOT_FOUND',
                'message' => 'Kartu Belum Terdaftar',
            ], 404);
        }

        $nis = $kartu->nis;

        // --------- Cek Siswa ---------
        $siswa = Siswa::where('nis', $nis)->where('status_aktif', 1)->first();
        if (!$siswa) {
            return response()->json([
                'ok' => false,
                'state' => 'NONAKTIF',
                'message' => 'Siswa Nonaktif / Tidak Ditemukan',
            ], 404);
        }

        // --------- Aturan Jadwal & Keterlambatan ---------
        // Senin-Kamis: 07.00–14.15
        // Jumat-Sabtu: 07.00–10.50
        // Terlambat jika jam_masuk > 07:15
        $dayOfWeek = (int) $nowWib->format('N'); // 1=Mon ... 7=Sun
        $isJumatAtauSabtu = in_array($dayOfWeek, [5,6], true);

        $jamMasukStart = Carbon::parse($tanggal.' 07:00:00', 'Asia/Jakarta');
        $jamPulangMin  = $isJumatAtauSabtu
            ? Carbon::parse($tanggal.' 10:50:00', 'Asia/Jakarta')
            : Carbon::parse($tanggal.' 14:15:00', 'Asia/Jakarta');

        $telatThreshold = Carbon::parse($tanggal.' 07:15:00', 'Asia/Jakarta');

        // --------- Idempotency per hari ---------
        try {
            DB::beginTransaction();

            $row = Absensi::firstOrNew([
                'nis'     => $nis,
                'tanggal' => $tanggal,
            ]);

            $row->sumber         = 'IOT';
            $row->kode_perangkat = $data['kode_perangkat'] ?? ($row->kode_perangkat ?: 'IOT-GATE');

            // Keputusan MASUK / PULANG:
            // - Jika jam_masuk masih kosong → coba set masuk (dengan validasi waktu)
            // - Jika jam_masuk sudah ada & jam_pulang kosong → coba set pulang (dengan validasi waktu)
            // - Jika keduanya sudah ada → tolak (DUP)
            if (empty($row->jam_masuk)) {
                // Validasi window masuk (boleh sebelum pulang min; minimal dari jamMasukStart → fleksibel)
                if ($nowWib->lt($jamMasukStart)) {
                    DB::rollBack();
                    return response()->json([
                        'ok' => false,
                        'state' => 'EARLY',
                        'message' => 'Belum Waktu Masuk',
                    ], 422);
                }

                $row->jam_masuk = $nowWib; // set jam_masuk
                // status_harian default HADIR saat alat (opsional, biar konsisten)
                $row->status_harian = $row->status_harian ?: 'HADIR';
                $row->save();

                DB::commit();

                $terlambat = $nowWib->gt($telatThreshold);

                return response()->json([
                    'ok'      => true,
                    'state'   => 'MASUK',
                    'message' => $terlambat ? 'Presensi Berhasil (Terlambat)' : 'Presensi Berhasil',
                    'terlambat' => $terlambat,
                    'nis'     => $nis,
                    'tanggal' => $tanggal,
                    'jam'     => $waktuHms,
                ]);
            }

            if (empty($row->jam_pulang)) {
                // Validasi minimal waktu pulang
                if ($nowWib->lt($jamPulangMin)) {
                    DB::rollBack();
                    return response()->json([
                        'ok' => false,
                        'state' => 'OUTSIDE',
                        'message' => 'Belum Waktunya Pulang',
                    ], 422);
                }

                $row->jam_pulang = $nowWib;
                $row->save();

                DB::commit();

                return response()->json([
                    'ok'      => true,
                    'state'   => 'PULANG',
                    'message' => 'Pulang Tercatat',
                    'nis'     => $nis,
                    'tanggal' => $tanggal,
                    'jam'     => $waktuHms,
                ]);
            }

            // Sudah punya masuk & pulang → tolak double
            DB::rollBack();
            return response()->json([
                'ok' => false,
                'state' => 'DUP',
                'message' => 'Sudah Terdata Hari Ini',
            ], 409);

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json([
                'ok' => false,
                'state' => 'SERVER',
                'message' => 'Server Error',
            ], 500);
        }
    }
}
