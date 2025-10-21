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

    // ============================
    // 🏠 DASHBOARD
    // ============================
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


    // ============================
    // 📚 MANAJEMEN ABSENSI
    // ============================
    // untuk log absensi
    Route::get('/absensi/log', [AbsensiController::class, 'log'])->name('absensi.log');
    // Halaman utama absensi (Input Manual + Tabel Hari Ini)
    Route::get('/absensi', [AbsensiController::class, 'index'])->name('absensi.index');

    Route::get('/absensi/edit', [AbsensiController::class, 'edit'])->name('absensi.edit');
    
    // Proses simpan input manual
    Route::post('/absensi/manual', [AbsensiController::class, 'storeManual'])->name('absensi.manual');
    Route::get('/absensi/input', [AbsensiController::class, 'index'])->name('absensi.input');
    // Proses untuk update & hapus
    Route::post('/absensi/{id}/update', [AbsensiController::class, 'update'])->name('absensi.update');
    Route::delete('/absensi/{id}', [AbsensiController::class, 'destroy'])->name('absensi.destroy');




    // ============================
    // 🧑‍🤝‍🧑 DATA MASTER
    // ============================
    Route::resource('siswa', SiswaController::class)->only([
        'index', 'create', 'store', 'edit', 'update', 'destroy'
    ]);

    Route::resource('kelas', KelasController::class)->only([
        'index', 'create', 'store', 'edit', 'update', 'destroy'
    ]);

    Route::resource('kartu', KartuController::class)->only([
        'index', 'store', 'destroy'
    ]);

    Route::resource('perangkat', PerangkatController::class)->only([
        'index', 'store', 'update', 'destroy'
    ]);


    // ============================
    // 📄 LAPORAN
    // ============================
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/unduh', [LaporanController::class, 'export'])->name('laporan.export');


    // ============================
    // ⚙ PENGATURAN
    // ============================
    Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
    Route::post('/pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');
});
    Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/admin/logs', function () {
    return view('adminlog.index');
})->name('admin.logs');
