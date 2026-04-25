<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FaceEnrollmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PresensiController;

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
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    });
});

// ================= LOGOUT =================
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');


use App\Http\Controllers\Admin\UserController;

Route::middleware(['auth','role:admin'])->group(function () {

    Route::get('/admin/users', [UserController::class, 'index']);
    Route::get('/admin/users/create', [UserController::class, 'create']);
    Route::post('/admin/users', [UserController::class, 'store']);
    Route::get('/admin/users/{id}/edit', [UserController::class, 'edit']);
    Route::post('/admin/users/{id}/update', [UserController::class, 'update']);
    Route::post('/admin/users/{id}/delete', [UserController::class, 'destroy']);

});
