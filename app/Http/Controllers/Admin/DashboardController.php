<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\Announcement;
use App\Models\Presensi;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserShift;
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

        $tanggalPresensiTerbaru = Presensi::query()->latest('tanggal')->value('tanggal');

        $tanggal = $request->filled('tanggal')
            ? Carbon::parse($request->input('tanggal'))->toDateString()
            : ($tanggalPresensiTerbaru
                ? Carbon::parse($tanggalPresensiTerbaru)->toDateString()
                : today()->toDateString());

        $presensiHarian = Presensi::whereDate('tanggal', $tanggal);
        $totalUser = User::where('role', 'user')->count();

        $totalPresensi = (clone $presensiHarian)->count();

        $hadir = (clone $presensiHarian)
            ->where('status', 'hadir')
            ->count();

        $telat = (clone $presensiHarian)
            ->whereIn('status', ['telat', 'terlambat'])
            ->count();

        $pulangCepat = (clone $presensiHarian)
            ->where('status_pulang', 'pulang_cepat')
            ->count();

        $izin = LeaveRequest::query()
            ->where('status', 'approved')
            ->whereDate('tanggal_mulai', '<=', $tanggal)
            ->whereDate('tanggal_selesai', '>=', $tanggal)
            ->count();

        $scheduledUserIds = UserShift::query()
            ->whereDate('tanggal', $tanggal)
            ->pluck('user_id')
            ->unique();

        $hadirUserIds = Presensi::query()
            ->whereDate('tanggal', $tanggal)
            ->pluck('user_id')
            ->unique();

        $izinUserIds = LeaveRequest::query()
            ->where('status', 'approved')
            ->whereDate('tanggal_mulai', '<=', $tanggal)
            ->whereDate('tanggal_selesai', '>=', $tanggal)
            ->pluck('user_id')
            ->unique();

        $alpha = $scheduledUserIds
            ->diff($hadirUserIds)
            ->diff($izinUserIds)
            ->count();

        $presensis = Presensi::with('user')
            ->whereDate('tanggal', $tanggal)
            ->orderByDesc('jam_masuk')
            ->orderByDesc('created_at')
            ->get();

        $workSetting = WorkSetting::first();
        $units = Unit::query()->count();
        $announcements = Announcement::query()
            ->where('is_published', true)
            ->whereDate('tanggal_mulai', '<=', $tanggal)
            ->whereDate('tanggal_berakhir', '>=', $tanggal)
            ->count();

        $chart = collect(range(6, 0))
            ->map(function ($minusDay) {
                $day = today()->subDays($minusDay)->toDateString();
                return [
                    'label' => Carbon::parse($day)->format('d/m'),
                    'hadir' => Presensi::query()->whereDate('tanggal', $day)->where('status', 'hadir')->count(),
                    'telat' => Presensi::query()->whereDate('tanggal', $day)->whereIn('status', ['telat', 'terlambat'])->count(),
                    'izin' => LeaveRequest::query()->where('status', 'approved')->whereDate('tanggal_mulai', '<=', $day)->whereDate('tanggal_selesai', '>=', $day)->count(),
                ];
            })
            ->all();

        return view('admin.dashboard', compact(
            'tanggal',
            'totalUser',
            'totalPresensi',
            'hadir',
            'telat',
            'pulangCepat',
            'izin',
            'alpha',
            'units',
            'announcements',
            'chart',
            'presensis',
            'workSetting'
        ));
    }
}
