<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// ===================== SCHEDULER ABSENSI =====================
$tz = config('attendance.timezone', 'Asia/Jakarta');

// Ping log tiap menit (indikator scheduler hidup)
Schedule::call(function () {
    \Illuminate\Support\Facades\Log::info('[SCHED] TICK '.now()->toDateTimeString());
})->timezone($tz)->everyMinute();

// AUTO-ALPA: tiap menit
Schedule::command('absensi:auto-alpa')
    ->timezone($tz)
    ->everyMinute()
    ->withoutOverlapping();

// AUTO-PULANG: tiap 5 menit
Schedule::command('absensi:auto-pulang')
    ->timezone($tz)
    ->everyFiveMinutes()
    ->withoutOverlapping();