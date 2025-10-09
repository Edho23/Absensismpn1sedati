<?php

use Illuminate\Support\Facades\Route;

// IMPORT CONTROLLERS:
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\KartuController;
use App\Http\Controllers\PerangkatController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PengaturanController;


Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('web')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Manajemen Absensi
    Route::get('/absensi', [AbsensiController::class, 'index'])->name('absensi.index');
    Route::post('/absensi/manual', [AbsensiController::class, 'storeManual'])->name('absensi.manual');

    // Data Master
    Route::resource('siswa', SiswaController::class)->only(['index','create','store','edit','update','destroy']);
    Route::resource('kelas', KelasController::class)->only(['index','create','store','edit','update','destroy']);
    Route::resource('kartu', KartuController::class)->only(['index','store','destroy']);
    Route::resource('perangkat', PerangkatController::class)->only(['index','store','update','destroy']);

    // Laporan & Pengaturan
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/unduh', [LaporanController::class, 'export'])->name('laporan.export');
    Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
    Route::post('/pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');
});

