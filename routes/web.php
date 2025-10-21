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

// =====================
// PUBLIC (GUEST) ROUTES
// =====================
Route::middleware('web')->group(function () {
    Route::get('/login',  [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login');

    // Logout (dukung GET/POST agar kompatibel dengan <a> atau <form>)
    Route::match(['get','post'], '/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

    // Root -> redirect ke dashboard (akan diarahkan ke login kalau belum auth)
    Route::get('/', fn () => redirect()->route('dashboard'));
});

// =====================
// PROTECTED (ADMIN) ROUTES
// =====================
Route::middleware([
    'web',
    'auth:admin',
    \App\Http\Middleware\LogAdminActivity::class, // atau 'log.admin' jika alias sudah aktif
])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Absensi
    Route::get('/absensi',        [AbsensiController::class, 'index'])->name('absensi.index');
    Route::get('/absensi/input',  [AbsensiController::class, 'index'])->name('absensi.input');
    Route::get('/absensi/edit',   [AbsensiController::class, 'edit'])->name('absensi.edit');
    Route::get('/absensi/log',    [AbsensiController::class, 'log'])->name('absensi.log');

    Route::post('/absensi/manual', [AbsensiController::class, 'storeManual'])->name('absensi.manual');

    // ✅ rute utama untuk update (cocok dengan @method('PUT') di Blade)
    Route::put('/absensi/{id}', [AbsensiController::class, 'update'])->name('absensi.update');

    // (opsional) kompatibilitas lama: kalau masih ada request POST lama ke /absensi/{id}/update
    Route::post('/absensi/{id}/update', [AbsensiController::class, 'update']);
 
    Route::delete('/absensi/{id}', [AbsensiController::class, 'destroy'])->name('absensi.destroy');

    // Data master
    Route::resource('siswa', SiswaController::class)->only(['index','create','store','edit','update','destroy']);
    Route::resource('kelas', KelasController::class)->only(['index','create','store','edit','update','destroy']);
    Route::resource('kartu', KartuController::class)->only(['index','store','destroy']);
    Route::resource('perangkat', PerangkatController::class)->only(['index','store','update','destroy']);

    // Laporan
    Route::get('/laporan',        [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/unduh',  [LaporanController::class, 'export'])->name('laporan.export');

    // Pengaturan
    Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
    Route::post('/pengaturan',[PengaturanController::class, 'update'])->name('pengaturan.update');

    // Log Admin (view statis saat ini)
    Route::get('/admin/logs', [AdminLogController::class, 'index'])->name('admin.logs');
});
