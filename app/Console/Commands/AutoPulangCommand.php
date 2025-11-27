<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Absensi;
use App\Models\HariLibur;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AutoPulangCommand extends Command
{
    protected $signature = 'absensi:auto-pulang {--date= : Tanggal target (YYYY-MM-DD), default today WIB}';
    protected $description = 'Set jam_pulang default utk siswa HADIR yg lupa tap pulang, sesuai jam dismissal per-hari.';

    public function handle(): int
    {
        $tz    = config('attendance.timezone', 'Asia/Jakarta');
        $today = $this->option('date') ?: Carbon::now($tz)->toDateString();

        $dowKey    = strtolower(Carbon::parse($today, $tz)->format('D')); // mon..sun
        $dismissal = (array) config('attendance.dismissal', []);
        $homeTime  = $dismissal[$dowKey] ?? null;

        if (empty($homeTime)) {
            $this->info("Tidak ada jam pulang utk hari {$dowKey}. Skip.");
            return self::SUCCESS;
        }

        // Hari libur? skip (termasuk Minggu)
        if ($this->isHoliday($today)) {
            $this->info('Hari libur — tidak diproses AutoPulang.');
            return self::SUCCESS;
        }

        $now      = Carbon::now($tz);
        $dismissDt = Carbon::parse("{$today} {$homeTime}", $tz);

        // Belum lewat jam pulang → keluar
        if ($now->lt($dismissDt)) {
            $this->info("Belum lewat jam pulang ({$homeTime}). Tidak ada aksi.");
            return self::SUCCESS;
        }

        // Update massal: status=HADIR, jam_pulang IS NULL
        $affected = Absensi::whereDate('tanggal', $today)
            ->where('status_harian', 'HADIR')
            ->whereNull('jam_pulang')
            ->update([
                'jam_pulang' => $dismissDt, // set ke jam pulang default
                'updated_at' => Carbon::now($tz),
            ]);

        $this->info("AutoPulang di-set untuk {$affected} baris.");
        return self::SUCCESS;
    }

    private function isHoliday(string $ymd): bool
    {
        $tz   = config('attendance.timezone', 'Asia/Jakarta');
        $date = Carbon::parse($ymd, $tz);

        if ((int) $date->isoWeekday() === 7) {
            return true;
        }

        $adaFixed = HariLibur::whereDate('tanggal', $date->toDateString())
            ->where('berulang', false)
            ->exists();
        if ($adaFixed) return true;

        $mmdd = $date->format('m-d');
        $adaRepeat = HariLibur::where('berulang', true)
            ->whereRaw("to_char(tanggal, 'MM-DD') = ?", [$mmdd])
            ->exists();

        return $adaRepeat;
    }
}
