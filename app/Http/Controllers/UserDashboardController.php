<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\User;
use App\Models\WorkSetting;
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
        $telat = (clone $rekapQuery)->where('status', 'telat')->count();
        $pulangCepat = (clone $rekapQuery)->where('status_pulang', 'pulang_cepat')->count();
        $totalPresensi = (clone $rekapQuery)->count();

        $recentPresensis = (clone $rekapQuery)
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        $workSetting = WorkSetting::first();

        return view('user.dashboard', compact(
            'presensiHariIni',
            'presensiTerakhir',
            'hadir',
            'telat',
            'pulangCepat',
            'totalPresensi',
            'workSetting',
            'recentPresensis'
        ));
    }
}
