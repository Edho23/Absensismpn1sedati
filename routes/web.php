<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\AdminLogController;
use App\Http\Controllers\{
    DashboardController,
    AbsensiController,
    SiswaController,
    KelasController,
    KartuController,
    PerangkatController,
    LaporanController,
    PengaturanController
};

/*
|--------------------------------------------------------------------------
| PUBLIC (GUEST) ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware('web')->group(function () {
    // Login
    Route::get('/login',  [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login');

    // Logout (dukung GET/POST agar kompatibel dengan <a> dan <form>)
    Route::match(['get','post'], '/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

    // Root -> redirect ke dashboard
    Route::get('/', fn () => redirect()->route('dashboard'));
});

/*
|--------------------------------------------------------------------------
| PROTECTED (ADMIN) ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware([
    'web',
    'auth:admin',
    \App\Http\Middleware\LogAdminActivity::class, // alias dari Kernel routeMiddleware
])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ===== ABSENSI =====
    Route::get('/absensi',        [AbsensiController::class, 'index'])->name('absensi.index');
    Route::get('/absensi/input',  [AbsensiController::class, 'index'])->name('absensi.input');
    Route::get('/absensi/edit',   [AbsensiController::class, 'edit'])->name('absensi.edit');
    Route::get('/absensi/log',    [AbsensiController::class, 'log'])->name('absensi.log');

    Route::post('/absensi/manual', [AbsensiController::class, 'storeManual'])->name('absensi.manual');

    // Update (pakai PUT di Blade @method('PUT'))
    Route::put('/absensi/{id}', [AbsensiController::class, 'update'])->name('absensi.update');
    // Kompat lama (jika masih ada form POST lama)
    Route::post('/absensi/{id}/update', [AbsensiController::class, 'update']);

    // Hapus
    Route::delete('/absensi/{id}', [AbsensiController::class, 'destroy'])->name('absensi.destroy');

    // ===== DATA MASTER =====
    Route::resource('siswa', SiswaController::class)->only(['index','create','store','edit','update','destroy']);
    Route::resource('kelas', KelasController::class)->only(['index','create','store','edit','update','destroy']);
    Route::resource('kartu', KartuController::class)->only(['index','store','destroy']);
    Route::resource('perangkat', PerangkatController::class)->only(['index','store','update','destroy']);

    // ===== ENDPOINT TYPEAHEAD NIS/NAMA =====
    Route::get('/siswa/search', [SiswaController::class, 'search'])->name('siswa.search');

    // ===== LAPORAN =====
    Route::get('/laporan',       [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/unduh', [LaporanController::class, 'export'])->name('laporan.export');

    // ===== LOG ADMIN =====
    Route::get('/admin/logs', [AdminLogController::class, 'index'])->name('admin.logs');
});
