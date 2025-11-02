<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RfidController;
use App\Http\Controllers\Api\SiswaSearchController;

// Ping sederhana untuk tes API di UI
Route::get('/ping', fn() => response('OK', 200));

// === RFID REGISTER (MODE DAFTAR KARTU) ===
Route::match(['get','post'], '/rfid/register-hit', [RfidController::class, 'registerHit']);
Route::get('/rfid/register-last', [RfidController::class, 'registerLast']);

// === PRESENSI LANGSUNG (opsional, kalau perangkat kirim ke sini) ===
Route::post('/rfid/presence', [RfidController::class, 'presenceHit']);

// === Typeahead NIS/Nama untuk UI (digunakan di halaman Kartu & Absensi Manual) ===
Route::get('/siswa/search', SiswaSearchController::class);
