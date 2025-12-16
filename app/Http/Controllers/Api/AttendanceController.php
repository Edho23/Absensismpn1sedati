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
    private function normUid(string $uid): string
    {
        $uid = strtoupper(trim($uid));
        return preg_replace('/[^0-9A-F]/', '', $uid) ?? '';
    }

    private function inWindow(Carbon $now, string $start, string $end): bool
    {
        $d = $now->toDateString();
        $s = Carbon::parse("$d $start", 'Asia/Jakarta');
        $e = Carbon::parse("$d $end", 'Asia/Jakarta');
        return $now->between($s, $e);
    }

    public function hit(Request $r)
    {
        $payload = $r->all();
        Log::info('ATTENDANCE_HIT_IN', ['payload' => $payload, 'ip' => $r->ip()]);

        try {
            if (!$r->filled('uid')) {
                return response()->json([
                    'ok' => false,
                    'state' => 'BAD_REQUEST',
                    'message' => 'uid wajib',
                ], 422);
            }

            // WIB time
            try {
                $nowWib = $r->filled('waktu')
                    ? Carbon::parse($r->input('waktu'))->setTimezone('Asia/Jakarta')
                    : Carbon::now('Asia/Jakarta');
            } catch (\Throwable $e) {
                $nowWib = Carbon::now('Asia/Jakarta');
            }

            $tanggal  = $nowWib->toDateString();
            $waktuHms = $nowWib->format('H:i:s');

            // Window jam dari ENV
            $inStart  = env('ATT_IN_START', '06:00');
            $inEnd    = env('ATT_IN_END',   '08:00');
            $outStart = env('ATT_OUT_START','13:30');
            $outEnd   = env('ATT_OUT_END',  '16:00');

            // BYPASS waktu HANYA dari ENV (tidak peduli "test":true)
            $bypassTime = (bool) env('ATTENDANCE_BYPASS_TIME', false);

            // UID normalize + cari kartu
            $uidIn   = strtoupper(trim((string) $r->input('uid')));
            $uidNorm = $this->normUid($uidIn);

            Log::info('UID_NORM', ['uid_in' => $uidIn, 'uid_norm' => $uidNorm]);

            $kartu = KartuRfid::query()
                ->whereRaw("REGEXP_REPLACE(UPPER(uid), '[^0-9A-F]', '', 'g') = ?", [$uidNorm])
                ->first();

            if (!$kartu) {
                Log::warning('CARD_NOT_FOUND', ['uid' => $uidIn, 'uid_norm' => $uidNorm]);
                return response()->json([
                    'ok' => false,
                    'state' => 'NOT_FOUND',
                    'message' => 'Kartu belum terdaftar',
                ], 404);
            }

            if (isset($kartu->status) && $kartu->status !== 'A') {
                return response()->json([
                    'ok' => false,
                    'state' => 'CARD_INACTIVE',
                    'message' => 'Kartu non-aktif',
                ], 403);
            }

            if (!$kartu->nis) {
                return response()->json([
                    'ok' => false,
                    'state' => 'CARD_NO_NIS',
                    'message' => 'Kartu tidak terkait NIS',
                ], 422);
            }

            $siswa = Siswa::where('nis', $kartu->nis)->first();
            if (!$siswa) {
                return response()->json([
                    'ok' => false,
                    'state' => 'SISWA_MISSING',
                    'message' => 'Siswa tidak ditemukan',
                ], 404);
            }

            $isAktif = true;
            if (\Schema::hasColumn('siswa', 'status')) {
                $isAktif = ($siswa->status === 'A');
            } elseif (\Schema::hasColumn('siswa', 'status_aktif')) {
                $isAktif = ((int) $siswa->status_aktif === 1);
            }
            if (!$isAktif) {
                return response()->json([
                    'ok' => false,
                    'state' => 'NONAKTIF',
                    'message' => 'Siswa non-aktif',
                ], 403);
            }

            DB::beginTransaction();

            $row = Absensi::firstOrNew([
                'nis'     => $siswa->nis,
                'tanggal' => $tanggal,
            ]);

            $row->sumber         = 'RFID';
            $row->status_harian  = $row->status_harian ?: 'HADIR';
            $row->kode_perangkat = $r->input('kode_perangkat', $row->kode_perangkat ?: 'IOT-GATE');

            // ===== MASUK =====
            if (empty($row->jam_masuk)) {
                if (!$bypassTime && !$this->inWindow($nowWib, $inStart, $inEnd)) {
                    DB::rollBack();
                    return response()->json([
                        'ok' => false,
                        'state' => 'EARLY_IN',
                        'message' => 'Belum waktu presensi masuk',
                    ], 422);
                }

                $row->jam_masuk = $nowWib;
                $row->save();
                DB::commit();

                return response()->json([
                    'ok' => true,
                    'state' => 'MASUK',
                    'message' => 'Presensi Masuk tercatat',
                    'nis' => $siswa->nis,
                    'tanggal' => $tanggal,
                    'jam' => $waktuHms,
                ], 200);
            }

            // ===== SUDAH MASUK, BELUM PULANG =====
            if (empty($row->jam_pulang)) {
                if (!$bypassTime && !$this->inWindow($nowWib, $outStart, $outEnd)) {
                    DB::rollBack();
                    return response()->json([
                        'ok' => false,
                        'state' => 'ALREADY_IN',
                        'message' => 'Sudah presensi masuk',
                        'nis' => $siswa->nis,
                        'tanggal' => $tanggal,
                    ], 409);
                }

                $row->jam_pulang = $nowWib;
                $row->save();
                DB::commit();

                return response()->json([
                    'ok' => true,
                    'state' => 'PULANG',
                    'message' => 'Presensi Pulang tercatat',
                    'nis' => $siswa->nis,
                    'tanggal' => $tanggal,
                    'jam' => $waktuHms,
                ], 200);
            }

            DB::rollBack();
            return response()->json([
                'ok' => false,
                'state' => 'ALREADY_OUT',
                'message' => 'Sudah presensi pulang hari ini',
            ], 409);

        } catch (\Throwable $e) {
            Log::error('ATTENDANCE_HIT_EX', ['err' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'ok' => false,
                'state' => 'SERVER',
                'message' => 'Server Error',
                'detail' => config('app.env') !== 'production' ? $e->getMessage() : null,
            ], 500);
        }
    }
}
