<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Absensi, Siswa, KartuRfid};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    /**
     * SENGAJA: Versi minimal untuk memastikan alur IoT → Server → DB BERHASIL.
     * - Tidak pakai aturan jam dahulu (bisa uji kapan pun).
     * - Tangkap error per langkah agar 500 tak terjadi diam-diam.
     *
     * POST /api/attendances/hit
     * Body JSON: { "uid": "...", "waktu": "ISO8601 (ops)", "kode_perangkat": "GATE-1 (ops)" }
     */
    public function hit(Request $r)
    {
        $payload = $r->all();
        Log::info('ATTENDANCE_HIT_IN', ['payload' => $payload, 'ip' => $r->ip()]);

        try {
            // --------- 1) VALIDASI RINGAN ---------
            if (!$r->filled('uid')) {
                return response()->json(['ok' => false, 'state' => 'BAD_REQUEST', 'message' => 'uid wajib'], 422);
            }

            // Waktu WIB (pakai payload jika ada, kalau gagal parse pakai now)
            try {
                $nowWib = $r->filled('waktu')
                    ? Carbon::parse($r->input('waktu'))->setTimezone('Asia/Jakarta')
                    : Carbon::now('Asia/Jakarta');
            } catch (\Throwable $e) {
                $nowWib = Carbon::now('Asia/Jakarta');
            }

            $tanggal  = $nowWib->toDateString();
            $waktuHms = $nowWib->format('H:i:s');

            // --------- 2) NORMALISASI UID + CARI KARTU ---------
            $uidIn = strtoupper(trim((string) $r->input('uid')));
            $uidNoColon = preg_replace('/[^0-9A-F]/', '', $uidIn); // buang selain HEX
            // Cari dua-duanya: uid apa adanya & uid tanpa colon
            $kartu = KartuRfid::where('uid', $uidIn)
                ->orWhereRaw("REPLACE(UPPER(uid), ':', '') = ?", [$uidNoColon])
                ->first();

            if (!$kartu) {
                Log::warning('CARD_NOT_FOUND', ['uid' => $uidIn, 'uid_nc' => $uidNoColon]);
                return response()->json(['ok' => false, 'state' => 'NOT_FOUND', 'message' => 'Kartu belum terdaftar'], 404);
            }

            // Wajib ada NIS
            if (!$kartu->nis) {
                Log::warning('CARD_HAS_NO_NIS', ['card_id' => $kartu->id, 'uid' => $kartu->uid]);
                return response()->json(['ok' => false, 'state' => 'CARD_NO_NIS', 'message' => 'Kartu tidak terkait NIS'], 422);
            }

            // --------- 3) CEK SISWA AKTIF (status = 'A' ATAU legacy status_aktif=1) ---------
            $siswa = Siswa::where('nis', $kartu->nis)->first();
            if (!$siswa) {
                Log::warning('SISWA_NOT_FOUND', ['nis' => $kartu->nis]);
                return response()->json(['ok' => false, 'state' => 'SISWA_MISSING', 'message' => 'Siswa tidak ditemukan'], 404);
            }

            $isAktif = null;
            if (\Schema::hasColumn('siswa', 'status')) {
                $isAktif = ($siswa->status === 'A');
            } elseif (\Schema::hasColumn('siswa', 'status_aktif')) {
                $isAktif = ((int) $siswa->status_aktif === 1);
            } else {
                // jika kolom tidak ada dua-duanya, anggap aktif untuk uji
                $isAktif = true;
            }

            if (!$isAktif) {
                Log::info('SISWA_NONAKTIF', ['nis' => $siswa->nis, 'status' => $siswa->status ?? null, 'status_aktif' => $siswa->status_aktif ?? null]);
                return response()->json(['ok' => false, 'state' => 'NONAKTIF', 'message' => 'Siswa non-aktif'], 403);
            }

            // --------- 4) SIMPAN ABSESNSI (IDEMPOTENT PER HARI) ---------
            DB::beginTransaction();

            $row = Absensi::firstOrNew([
                'nis'     => $siswa->nis,
                'tanggal' => $tanggal,
            ]);

            $row->sumber         = 'RFID';
            $row->status_harian  = $row->status_harian ?: 'HADIR';
            $row->kode_perangkat = $r->input('kode_perangkat', $row->kode_perangkat ?: 'IOT-GATE');

            if (empty($row->jam_masuk)) {
                $row->jam_masuk = $nowWib;
                $row->save();
                DB::commit();

                Log::info('MASUK_OK', ['nis' => $siswa->nis, 'tanggal' => $tanggal, 'jam' => $waktuHms]);
                return response()->json([
                    'ok'      => true,
                    'state'   => 'MASUK',
                    'message' => 'Presensi Masuk tercatat',
                    'nis'     => $siswa->nis,
                    'tanggal' => $tanggal,
                    'jam'     => $waktuHms,
                ], 200);
            }

            if (empty($row->jam_pulang)) {
                $row->jam_pulang = $nowWib;
                $row->save();
                DB::commit();

                Log::info('PULANG_OK', ['nis' => $siswa->nis, 'tanggal' => $tanggal, 'jam' => $waktuHms]);
                return response()->json([
                    'ok'      => true,
                    'state'   => 'PULANG',
                    'message' => 'Presensi Pulang tercatat',
                    'nis'     => $siswa->nis,
                    'tanggal' => $tanggal,
                    'jam'     => $waktuHms,
                ], 200);
            }

            DB::rollBack();
            Log::info('DUP_DAY', ['nis' => $siswa->nis, 'tanggal' => $tanggal]);
            return response()->json([
                'ok'      => false,
                'state'   => 'DUP',
                'message' => 'Sudah lengkap (masuk & pulang) untuk hari ini',
            ], 409);

        } catch (\Throwable $e) {
            Log::error('ATTENDANCE_HIT_EX', ['err' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            // Di DEV, jika mau, tampilkan error detail (sementara):
            if (config('app.env') !== 'production') {
                return response()->json([
                    'ok'      => false,
                    'state'   => 'SERVER',
                    'message' => 'Server Error',
                    'detail'  => $e->getMessage(),
                ], 500);
            }
            return response()->json(['ok' => false, 'state' => 'SERVER', 'message' => 'Server Error'], 500);
        }
    }
}
