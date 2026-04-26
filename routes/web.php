<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FaceEnrollmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WorkSettingController;

// ================= USER LOGIN =================
Route::view('/', 'landing.welcome')->name('landing');
Route::get('/login', [AuthController::class, 'showUserLogin'])->name('login');
Route::post('/login', [AuthController::class, 'userLogin']);

// ================= ADMIN LOGIN =================
Route::get('/admin/login', [AuthController::class, 'showAdminLogin']);
Route::post('/admin/login', [AuthController::class, 'adminLogin']);

// ================= USER =================
Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/face/enroll', [FaceEnrollmentController::class, 'show'])->name('face.enroll');
    Route::post('/face/enroll', [FaceEnrollmentController::class, 'store'])->name('face.enroll.store');
    Route::get('/face/verification-progress', [FaceEnrollmentController::class, 'showVerificationProgress'])->name('face.verify.progress');
    Route::get('/face/success', [FaceEnrollmentController::class, 'showSuccess'])->name('face.success');
    Route::get('/absen', [PresensiController::class, 'show'])->name('absen.page');
    Route::post('/absen/challenge', [PresensiController::class, 'challenge'])->name('absen.challenge');
    Route::post('/absen', [PresensiController::class, 'absen'])->name('absen.store');

    Route::get('/dashboard', function () {
        if (!auth()->user()->hasFaceEnrollment()) {
            return redirect()->route('face.enroll');
        }

        return view('user.dashboard');
    })->name('dashboard');
});

// ================= ADMIN =================
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/presensi', [DashboardController::class, 'index'])->name('presensi.index');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::post('/users/{id}/update', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{id}/delete', [UserController::class, 'destroy'])->name('users.destroy');

        Route::get('/settings/work', [WorkSettingController::class, 'edit'])->name('settings.work.edit');
        Route::post('/settings/work', [WorkSettingController::class, 'update'])->name('settings.work.update');
});

// ================= LOGOUT =================
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');
