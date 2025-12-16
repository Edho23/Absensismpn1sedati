<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Siswa;
use App\Models\Absensi;
use App\Models\HariLibur;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AutoAlpaCommand extends Command
{
    protected $signature   = 'absensi:auto-alpa {--date= : Tanggal target (YYYY-MM-DD), default today WIB}';
    protected $description = 'Tandai ALPA untuk siswa yang belum tap setelah jam cutoff, kecuali hari libur/Minggu';

    public function handle(): int
    {
        $tz    = config('attendance.timezone', 'Asia/Jakarta');
        $today = $this->option('date') ?: Carbon::now($tz)->toDateString();

        // --- Guard: kunci harian agar job TIDAK JALAN dua kali di hari yang sama ---
        $lockKey = "autoalpa:{$today}";
        // add() hanya akan set kalau belum ada; expired di 23:59:59 WIB hari ini
        $expireAt = Carbon::parse($today.' 23:59:59', $tz);
        if (!Cache::add($lockKey, 1, $expireAt)) {
            $this->info("Auto-ALPA sudah pernah dijalankan pada {$today}. Skip.");
            return self::SUCCESS;
        }

        // Map cutoff per-hari dari config
        $dowKey = strtolower(Carbon::parse($today, $tz)->format('D')); // mon..sun
        $map    = (array) config('attendance.cutoff', []);
        $cutoff = $map[$dowKey] ?? null;

        // Tidak ada cutoff (Minggu) → keluar
        if (empty($cutoff)) {
            $this->info("Tidak ada cutoff utk hari {$dowKey}. Skip.");
            return self::SUCCESS;
        }

        $now      = Carbon::now($tz);
        $cutoffDt = Carbon::parse("{$today} {$cutoff}", $tz);

        // Belum lewat cutoff → keluar
        if ($now->lt($cutoffDt)) {
            $this->info("Belum lewat cutoff ({$cutoff}). Tidak ada aksi.");
            return self::SUCCESS;
        }

        // Hari libur? skip
        if ($this->isHoliday($today)) {
            $this->info('Hari libur — tidak diproses.');
            return self::SUCCESS;
        }

        // Semua siswa aktif
        $siswaAktif = Siswa::aktif()->pluck('nis');

        // Yang belum punya baris absensi hari ini
        $sudahAda = Absensi::whereDate('tanggal', $today)->pluck('nis');
        $belumAda = $siswaAktif->diff($sudahAda);

        if ($belumAda->isEmpty()) {
            $this->info('Semua siswa sudah memiliki baris absensi hari ini. Tidak ada yang ALPA.');
            return self::SUCCESS;
        }

        // Siapkan rows (catatan DIKOSONGKAN sesuai permintaan)
        $nowTs = Carbon::now($tz);
        $rows  = [];
        foreach ($belumAda as $nis) {
            $rows[] = [
                'nis'            => $nis,
                'tanggal'        => $today,
                'jam_masuk'      => null,
                'jam_pulang'     => null,
                'terlambat'      => false,
                'status_harian'  => 'ALPA',
                'sumber'         => 'MANUAL',
                'catatan'        => null,            // ← kosong
                'kode_perangkat' => 'AUTO-ALPA',
                'created_at'     => $nowTs,
                'updated_at'     => $nowTs,
            ];
        }

        // Gunakan upsert agar aman kalau dipanggil dobel (unik: nis+tanggal)
        DB::table('absensi')->upsert($rows, ['nis','tanggal'], []);

        $this->info('Ditandai ALPA: '.$belumAda->count().' siswa.');
        return self::SUCCESS;
    }

    private function isHoliday(string $ymd): bool
    {
        $tz   = config('attendance.timezone', 'Asia/Jakarta');
        $date = Carbon::parse($ymd, $tz);

        // Minggu dianggap libur
        if ((int) $date->isoWeekday() === 7) {
            return true;
        }

        // Libur spesifik (tahun sama)
        $adaFixed = HariLibur::whereDate('tanggal', $date->toDateString())
            ->where('berulang', false)
            ->exists();
        if ($adaFixed) return true;

        // Libur berulang (MM-DD)
        $mmdd = $date->format('m-d');
        $adaRepeat = HariLibur::where('berulang', true)
            ->whereRaw("to_char(tanggal, 'MM-DD') = ?", [$mmdd])
            ->exists();

        return $adaRepeat;
    }
}
