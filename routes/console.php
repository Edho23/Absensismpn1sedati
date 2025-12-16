<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

/**
 * ===================== SCHEDULER ABSENSI =====================
 * Jalankan SEKALI per hari pada jam cutoff masing-masing hari.
 * Ambil jam dari config('attendance.cutoff.*').
 */
$tz   = config('attendance.timezone', 'Asia/Jakarta');
$cut  = (array) config('attendance.cutoff', []); // mon..sun

// indikator scheduler hidup (boleh dibiarkan)
Schedule::call(function () {
    \Illuminate\Support\Facades\Log::info('[SCHED] TICK '.now()->toDateTimeString());
})->timezone($tz)->everyMinute();

// AUTO-ALPA — sekali per hari di jam cutoff
if (!empty($cut['mon'])) Schedule::command('absensi:auto-alpa')->timezone($tz)->mondays()->dailyAt($cut['mon'])->withoutOverlapping();
if (!empty($cut['tue'])) Schedule::command('absensi:auto-alpa')->timezone($tz)->tuesdays()->dailyAt($cut['tue'])->withoutOverlapping();
if (!empty($cut['wed'])) Schedule::command('absensi:auto-alpa')->timezone($tz)->wednesdays()->dailyAt($cut['wed'])->withoutOverlapping();
if (!empty($cut['thu'])) Schedule::command('absensi:auto-alpa')->timezone($tz)->thursdays()->dailyAt($cut['thu'])->withoutOverlapping();
if (!empty($cut['fri'])) Schedule::command('absensi:auto-alpa')->timezone($tz)->fridays()->dailyAt($cut['fri'])->withoutOverlapping();
if (!empty($cut['sat'])) Schedule::command('absensi:auto-alpa')->timezone($tz)->saturdays()->dailyAt($cut['sat'])->withoutOverlapping();
// Minggu (sun) dikosongkan → tidak dijadwalkan

/**
 * AUTO-PULANG — contoh: juga sekali di jam pulang (opsional).
 * Kalau mau tetap periodik (tiap 5 menit), tidak apa-apa—tapi sebaiknya pakai guard juga.
 */
$dis = (array) config('attendance.dismissal', []);
if (!empty($dis['mon'])) Schedule::command('absensi:auto-pulang')->timezone($tz)->mondays()->dailyAt($dis['mon'])->withoutOverlapping();
if (!empty($dis['tue'])) Schedule::command('absensi:auto-pulang')->timezone($tz)->tuesdays()->dailyAt($dis['tue'])->withoutOverlapping();
if (!empty($dis['wed'])) Schedule::command('absensi:auto-pulang')->timezone($tz)->wednesdays()->dailyAt($dis['wed'])->withoutOverlapping();
if (!empty($dis['thu'])) Schedule::command('absensi:auto-pulang')->timezone($tz)->thursdays()->dailyAt($dis['thu'])->withoutOverlapping();
if (!empty($dis['fri'])) Schedule::command('absensi:auto-pulang')->timezone($tz)->fridays()->dailyAt($dis['fri'])->withoutOverlapping();
if (!empty($dis['sat'])) Schedule::command('absensi:auto-pulang')->timezone($tz)->saturdays()->dailyAt($dis['sat'])->withoutOverlapping();
// Minggu tidak dijadwalkan
