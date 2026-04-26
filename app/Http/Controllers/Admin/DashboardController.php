<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Models\User;
use App\Models\WorkSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'tanggal' => ['nullable', 'date'],
        ]);

        $tanggal = $request->filled('tanggal')
            ? Carbon::parse($request->input('tanggal'))->toDateString()
            : today()->toDateString();

        $presensiHarian = Presensi::whereDate('tanggal', $tanggal);
        $totalUser = User::count();

        $totalPresensi = (clone $presensiHarian)->count();

        $hadir = (clone $presensiHarian)
            ->where('status', 'hadir')
            ->count();

        $telat = (clone $presensiHarian)
            ->where('status', 'telat')
            ->count();

        $pulangCepat = (clone $presensiHarian)
            ->where('status_pulang', 'pulang_cepat')
            ->count();

        $presensis = Presensi::with('user')
            ->whereDate('tanggal', $tanggal)
            ->orderByDesc('jam_masuk')
            ->orderByDesc('created_at')
            ->get();

        $workSetting = WorkSetting::first();

        return view('admin.dashboard', compact(
            'tanggal',
            'totalUser',
            'totalPresensi',
            'hadir',
            'telat',
            'pulangCepat',
            'presensis',
            'workSetting'
        ));
    }
}
