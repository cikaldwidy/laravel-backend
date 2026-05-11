<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\LeaveRequest;
use App\Models\Presensi;
use App\Models\ShiftSchedule;
use App\Models\User;
use App\Models\WorkSetting;
use App\Support\ShiftTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class UserDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user instanceof User) {
            abort(403);
        }

        if (!$user->hasFaceEnrollment()) {
            return redirect()->route('face.enroll');
        }

        $presensiHariIni = Presensi::where('user_id', $user->id)
            ->whereDate('tanggal', today())
            ->first();
        $presensiTerakhir = Presensi::where('user_id', $user->id)
            ->latest('tanggal')
            ->latest('created_at')
            ->first();

        $rekapQuery = Presensi::where('user_id', $user->id)
            ->whereDate('tanggal', '>=', today()->subDays(29));

        $hadir = (clone $rekapQuery)->where('status', 'hadir')->count();
        $telat = (clone $rekapQuery)->whereIn('status', ['telat', 'terlambat'])->count();
        $pulangCepat = (clone $rekapQuery)->where('status_pulang', 'pulang_cepat')->count();
        $izin = (clone $rekapQuery)->where('status', 'izin')->count();
        $totalPresensi = (clone $rekapQuery)->count();

        $recentPresensis = (clone $rekapQuery)
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        $workSetting = WorkSetting::first();
        $checkinEarlyMinutes = (int) ($workSetting?->checkin_early_minutes ?? WorkSetting::DEFAULT_CHECKIN_EARLY_MINUTES);
        $checkoutLateMinutes = (int) ($workSetting?->checkout_late_minutes ?? WorkSetting::DEFAULT_CHECKOUT_LATE_MINUTES);

        // Shift for display (RS): jadwal shift hari ini dan shift aktif (untuk shift malam lintas hari).
        $activeShift = null;
        $scheduledShift = null;
        $now = now();

        $todayAssignment = ShiftSchedule::query()
            ->where('user_id', $user->id)
            ->whereDate('tanggal', $now->toDateString())
            ->first();

        $scheduledShift = $todayAssignment;

        $candidates = ShiftSchedule::query()
            ->where('user_id', $user->id)
            ->where('status', 'aktif')
            ->whereIn('tanggal', [
                $now->toDateString(),
                $now->copy()->subDay()->toDateString(),
            ])
            ->get();

        foreach ($candidates as $candidate) {
            $shiftDate = Carbon::parse($candidate->tanggal)->startOfDay();
            $window = ShiftTime::window(
                $shiftDate,
                $candidate->jam_masuk->format('H:i:s'),
                $candidate->jam_pulang->format('H:i:s'),
                $checkinEarlyMinutes,
                $checkoutLateMinutes
            );

            if ($now->between($window['allowed_start'], $window['allowed_end'], true)) {
                $activeShift = $candidate;
                break;
            }
        }

        $approvedLeaveToday = LeaveRequest::query()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereDate('tanggal_mulai', '<=', today())
            ->whereDate('tanggal_selesai', '>=', today())
            ->latest('approved_at')
            ->first();

        $announcements = Announcement::query()
            ->with('unit')
            ->where('is_published', true)
            ->whereDate('tanggal_mulai', '<=', today())
            ->whereDate('tanggal_berakhir', '>=', today())
            ->where(function ($query) use ($user) {
                $query->where('target_type', 'all');

                if ($user->employeeDetail?->unit_id) {
                    $query->orWhere(function ($unitQuery) use ($user) {
                        $unitQuery->where('target_type', 'unit')
                            ->where('unit_id', $user->employeeDetail?->unit_id);
                    });
                }
            })
            ->latest('tanggal_mulai')
            ->limit(5)
            ->get();

        return view('user.dashboard', compact(
            'presensiHariIni',
            'presensiTerakhir',
            'hadir',
            'telat',
            'pulangCepat',
            'izin',
            'totalPresensi',
            'workSetting',
            'recentPresensis',
            'activeShift',
            'scheduledShift',
            'approvedLeaveToday',
            'announcements'
        ));
    }
}
