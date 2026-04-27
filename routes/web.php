<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FaceEnrollmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\UserBiodataController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WorkSettingController;
use App\Http\Controllers\Admin\ShiftController;
use App\Http\Controllers\Admin\UserShiftController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\LeaveRequestController as AdminLeaveRequestController;
use App\Http\Controllers\Admin\AttendanceHistoryController;
use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserBiodataController as AdminUserBiodataController;

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

    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
    Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
    Route::get('/izin', [LeaveRequestController::class, 'index'])->name('leave_requests.index');
    Route::get('/izin/create', [LeaveRequestController::class, 'create'])->name('leave_requests.create');
    Route::post('/izin', [LeaveRequestController::class, 'store'])->name('leave_requests.store');
    Route::post('/izin/{leaveRequest}/delete', [LeaveRequestController::class, 'destroy'])->name('leave_requests.destroy');
    Route::get('/pengumuman', [AnnouncementController::class, 'index'])->name('announcements.index');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [UserBiodataController::class, 'index'])->name('profile.index');
    Route::get('/profile/edit', [UserBiodataController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [UserBiodataController::class, 'update'])->name('profile.update');
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

        Route::get('/units', [UnitController::class, 'index'])->name('units.index');
        Route::post('/units', [UnitController::class, 'store'])->name('units.store');
        Route::put('/units/{unit}', [UnitController::class, 'update'])->name('units.update');
        Route::delete('/units/{unit}', [UnitController::class, 'destroy'])->name('units.destroy');

        Route::get('/settings/work', [WorkSettingController::class, 'edit'])->name('settings.work.edit');
        Route::post('/settings/work', [WorkSettingController::class, 'update'])->name('settings.work.update');

        // ===== SHIFT (RS) =====
        Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.index');
        Route::get('/shifts/create', [ShiftController::class, 'create'])->name('shifts.create');
        Route::post('/shifts', [ShiftController::class, 'store'])->name('shifts.store');
        Route::get('/shifts/{shift}/edit', [ShiftController::class, 'edit'])->name('shifts.edit');
        Route::put('/shifts/{shift}', [ShiftController::class, 'update'])->name('shifts.update');
        Route::delete('/shifts/{shift}', [ShiftController::class, 'destroy'])->name('shifts.destroy');

        // Jadwal shift per user per tanggal
        Route::get('/user-shifts', [UserShiftController::class, 'index'])->name('user_shifts.index');
        Route::post('/user-shifts', [UserShiftController::class, 'store'])->name('user_shifts.store');

        // Biodata user (admin manage)
        Route::get('/biodata', [AdminUserBiodataController::class, 'index'])->name('biodata.index');
        Route::get('/biodata/{user}/edit', [AdminUserBiodataController::class, 'edit'])->name('biodata.edit');
        Route::post('/biodata/{user}', [AdminUserBiodataController::class, 'update'])->name('biodata.update');
        Route::post('/biodata/{user}/delete', [AdminUserBiodataController::class, 'destroy'])->name('biodata.destroy');

        Route::get('/izin', [AdminLeaveRequestController::class, 'index'])->name('leave_requests.index');
        Route::post('/izin/{leaveRequest}', [AdminLeaveRequestController::class, 'update'])->name('leave_requests.update');

        Route::get('/histories', [AttendanceHistoryController::class, 'index'])->name('histories.index');

        Route::get('/announcements', [AdminAnnouncementController::class, 'index'])->name('announcements.index');
        Route::post('/announcements', [AdminAnnouncementController::class, 'store'])->name('announcements.store');
        Route::put('/announcements/{announcement}', [AdminAnnouncementController::class, 'update'])->name('announcements.update');
        Route::delete('/announcements/{announcement}', [AdminAnnouncementController::class, 'destroy'])->name('announcements.destroy');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/excel', [ReportController::class, 'exportExcel'])->name('reports.excel');
        Route::get('/reports/pdf', [ReportController::class, 'exportPdf'])->name('reports.pdf');
});

// ================= LOGOUT =================
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');
