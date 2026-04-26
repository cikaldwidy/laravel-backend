<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\UserShift;
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
        $totalPresensi = (clone $rekapQuery)->count();

        $recentPresensis = (clone $rekapQuery)
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        $workSetting = WorkSetting::first();

        // Shift for display (RS): jadwal shift hari ini dan shift aktif (untuk shift malam lintas hari).
        $activeShift = null;
        $scheduledShift = null;
        $now = now();

        $todayAssignment = UserShift::query()
            ->with('shift')
            ->where('user_id', $user->id)
            ->whereDate('tanggal', $now->toDateString())
            ->first();

        $scheduledShift = $todayAssignment?->shift;

        $candidates = UserShift::query()
            ->with('shift')
            ->where('user_id', $user->id)
            ->whereIn('tanggal', [
                $now->toDateString(),
                $now->copy()->subDay()->toDateString(),
            ])
            ->get();

        foreach ($candidates as $candidate) {
            if (!$candidate->shift) {
                continue;
            }

            $shiftDate = Carbon::parse($candidate->tanggal)->startOfDay();
            $window = ShiftTime::window($shiftDate, $candidate->shift->jam_masuk, $candidate->shift->jam_pulang, 60, 180);

            if ($now->between($window['allowed_start'], $window['allowed_end'], true)) {
                $activeShift = $candidate->shift;
                break;
            }
        }

        return view('user.dashboard', compact(
            'presensiHariIni',
            'presensiTerakhir',
            'hadir',
            'telat',
            'pulangCepat',
            'totalPresensi',
            'workSetting',
            'recentPresensis',
            'activeShift',
            'scheduledShift'
        ));
    }
}
