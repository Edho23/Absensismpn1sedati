protected function schedule(\Illuminate\Console\Scheduling\Schedule $schedule): void
{
    // Auto-pulang 14:30 untuk Sen–Kam, 11:00 untuk Jum–Sab
    $schedule->call(function () {
        $now = now();
        $isFriOrSat = in_array($now->dayOfWeekIso, [5,6]);
        $deadline = $isFriOrSat ? $now->copy()->setTime(11,0) : $now->copy()->setTime(14,30);
        if ($now->lessThan($deadline)) return;

        \App\Models\Absensi::whereDate('tanggal', $now->toDateString())
            ->whereNull('jam_pulang')
            ->update([
                'jam_pulang' => $now,
                'updated_at' => $now,
            ]);
    })->everyFifteenMinutes();
}
