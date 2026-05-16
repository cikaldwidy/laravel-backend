<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\Mobile\AuthController as MobileAuthController;
use App\Http\Controllers\Api\Mobile\AnnouncementController as MobileAnnouncementController;
use App\Http\Controllers\Api\Mobile\DashboardController as MobileDashboardController;
use App\Http\Controllers\Api\Mobile\IzinController as MobileIzinController;
use App\Http\Controllers\Api\Mobile\PresensiController as MobilePresensiController;
use App\Http\Controllers\Api\Mobile\ShiftController as MobileShiftController;
use App\Http\Controllers\Api\Mobile\ShiftSwapController as MobileShiftSwapController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::get('/profile', [ProfileController::class, 'show']);
    });
});

Route::post('/login', [MobileAuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [MobileAuthController::class, 'logout']);

    Route::prefix('user')->group(function () {
        Route::get('/profile', [MobileAuthController::class, 'profile']);
        Route::get('/dashboard', [MobileDashboardController::class, 'index']);
        Route::post('/presensi/masuk', [MobilePresensiController::class, 'masuk']);
        Route::post('/presensi/pulang', [MobilePresensiController::class, 'pulang']);
        Route::get('/presensi/riwayat', [MobilePresensiController::class, 'riwayat']);
        Route::get('/izin', [MobileIzinController::class, 'index']);
        Route::post('/izin', [MobileIzinController::class, 'store']);
        Route::get('/pengumuman', [MobileAnnouncementController::class, 'index']);
        Route::get('/jadwal-shift', [MobileShiftController::class, 'index']);
        Route::get('/tukar-shift', [MobileShiftSwapController::class, 'index']);
        Route::get('/tukar-shift/options', [MobileShiftSwapController::class, 'options']);
        Route::get('/tukar-shift/target-shifts', [MobileShiftSwapController::class, 'availableTargetShifts']);
        Route::post('/tukar-shift', [MobileShiftSwapController::class, 'store']);
        Route::post('/tukar-shift/{shiftSwap}/accept', [MobileShiftSwapController::class, 'accept']);
        Route::post('/tukar-shift/{shiftSwap}/reject', [MobileShiftSwapController::class, 'reject']);
    });
});
