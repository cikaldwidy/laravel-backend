<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FeaturePageController;
use App\Http\Controllers\User\AnnouncementController;
use App\Http\Controllers\User\FaceEnrollmentController;
use App\Http\Controllers\User\HistoryController;
use App\Http\Controllers\User\LeaveRequestController;
use App\Http\Controllers\User\PresensiController;
use App\Http\Controllers\User\ShiftController as UserShiftScheduleController;
use App\Http\Controllers\User\ShiftSwapController;
use App\Http\Controllers\User\UserBiodataController;
use App\Http\Controllers\User\UserDashboardController;
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
use App\Http\Controllers\Admin\ShiftManagementController;
use App\Http\Controllers\Admin\UserBiodataController as AdminUserBiodataController;
use App\Http\Controllers\Admin\FaceDataController;
use App\Http\Controllers\Admin\FeatureSettingController;

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
    Route::post('/absen', [PresensiController::class, 'absen'])->name('absen.store');

    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
    Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
    Route::get('/izin', [LeaveRequestController::class, 'index'])->name('leave_requests.index');
    Route::get('/izin/create', [LeaveRequestController::class, 'create'])->name('leave_requests.create');
    Route::post('/izin', [LeaveRequestController::class, 'store'])->name('leave_requests.store');
    Route::post('/izin/{leaveRequest}/delete', [LeaveRequestController::class, 'destroy'])->name('leave_requests.destroy');
    Route::get('/pengumuman', [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('/jadwal-shift', [UserShiftScheduleController::class, 'index'])->name('user.shifts.index');
    Route::get('/fitur/{featureKey}', [FeaturePageController::class, 'user'])
        ->whereIn('featureKey', ['sakit', 'cuti', 'istirahat', 'lembur', 'slip_gaji'])
        ->middleware('feature.access:{featureKey}')
        ->name('features.show');
    Route::get('/tukar-shift', [ShiftSwapController::class, 'index'])->name('shift-swaps.index');
    Route::get('/tukar-shift/create', [ShiftSwapController::class, 'create'])->name('shift-swaps.create');
    Route::get('/tukar-shift/target-shifts', [ShiftSwapController::class, 'availableTargetShifts'])->name('shift-swaps.target-shifts');
    Route::post('/tukar-shift', [ShiftSwapController::class, 'store'])->name('shift-swaps.store');
    Route::post('/tukar-shift/{shiftSwap}/accept', [ShiftSwapController::class, 'targetAccept'])->name('shift-swaps.accept');
    Route::post('/tukar-shift/{shiftSwap}/reject', [ShiftSwapController::class, 'targetReject'])->name('shift-swaps.reject');
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
        Route::get('/settings/features', [FeatureSettingController::class, 'index'])->name('settings.features.index');
        Route::post('/settings/features', [FeatureSettingController::class, 'update'])->name('settings.features.update');
        Route::get('/fitur/{featureKey}', [FeaturePageController::class, 'admin'])
            ->whereIn('featureKey', ['sakit', 'cuti', 'istirahat', 'lembur', 'slip_gaji'])
            ->middleware('feature.access:{featureKey}')
            ->name('features.show');

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
        Route::get('/shift-management/schedules', [ShiftManagementController::class, 'schedules'])->name('shift_management.schedules');
        Route::post('/shift-management/schedules', [ShiftManagementController::class, 'storeSchedule'])->name('shift_management.schedules.store');
        Route::put('/shift-management/schedules/{shiftSchedule}', [ShiftManagementController::class, 'updateSchedule'])->name('shift_management.schedules.update');
        Route::delete('/shift-management/schedules/{shiftSchedule}', [ShiftManagementController::class, 'destroySchedule'])->name('shift_management.schedules.destroy');
        Route::post('/shift-management/schedules/bulk-assign', [ShiftManagementController::class, 'bulkAssign'])->name('shift_management.schedules.bulk_assign');
        Route::get('/shift-management/swaps', [ShiftManagementController::class, 'swaps'])->name('shift_management.swaps');
        Route::post('/shift-management/swaps/{shiftSwap}/approve', [ShiftManagementController::class, 'approveSwap'])->name('shift_management.swaps.approve');
        Route::post('/shift-management/swaps/{shiftSwap}/reject', [ShiftManagementController::class, 'rejectSwap'])->name('shift_management.swaps.reject');

        // Biodata user (admin manage)
        Route::get('/biodata', [AdminUserBiodataController::class, 'index'])->name('biodata.index');
        Route::get('/biodata/{user}/edit', [AdminUserBiodataController::class, 'edit'])->name('biodata.edit');
        Route::post('/biodata/{user}', [AdminUserBiodataController::class, 'update'])->name('biodata.update');
        Route::post('/biodata/{user}/delete', [AdminUserBiodataController::class, 'destroy'])->name('biodata.destroy');

        Route::get('/face-data', [FaceDataController::class, 'index'])->name('face_data.index');
        Route::post('/face-data', [FaceDataController::class, 'store'])->name('face_data.store');
        Route::put('/face-data/{faceEmbedding}', [FaceDataController::class, 'update'])->name('face_data.update');
        Route::delete('/face-data/{faceEmbedding}', [FaceDataController::class, 'destroy'])->name('face_data.destroy');

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
