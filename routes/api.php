<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttendanceController;

Route::post('/attendances/hit', [AttendanceController::class, 'hit']);
