<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\Announcement;
use App\Models\Presensi;
use App\Models\User;
use App\Models\ShiftSchedule;
use App\Models\ShiftSwap;
use App\Models\WorkSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'tanggal' => ['nullable', 'date'],
            'chart_period' => ['nullable', 'in:7_days,1_month,1_year'],
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

        $scheduledUserIds = ShiftSchedule::query()
            ->whereDate('tanggal', $tanggal)
            ->where('status', 'aktif')
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
        $units = Department::query()->count();
        $announcements = Announcement::query()
            ->where('is_published', true)
            ->whereDate('tanggal_mulai', '<=', $tanggal)
            ->whereDate('tanggal_berakhir', '>=', $tanggal)
            ->count();

        $totalShiftHariIni = ShiftSchedule::query()
            ->whereDate('tanggal', $tanggal)
            ->where('status', 'aktif')
            ->count();

        $userMasukHariIni = Presensi::query()
            ->whereDate('tanggal', $tanggal)
            ->distinct('user_id')
            ->count('user_id');

        $swapPending = ShiftSwap::query()
            ->where('status', 'pending')
            ->count();

        $chartPeriod = $request->input('chart_period', '7_days');
        $chartTitle = match ($chartPeriod) {
            '1_month' => 'Grafik Kehadiran 1 Bulan',
            '1_year' => 'Grafik Kehadiran 1 Tahun',
            default => 'Grafik Kehadiran 7 Hari',
        };

        $chart = match ($chartPeriod) {
            '1_month' => collect(range(29, 0))
                ->map(function ($minusDay) {
                    $day = today()->subDays($minusDay)->toDateString();

                    return $this->chartPointForDay($day, Carbon::parse($day)->format('d/m'));
                })
                ->all(),
            '1_year' => collect(range(11, 0))
                ->map(function ($minusMonth) {
                    $month = today()->startOfMonth()->subMonths($minusMonth);
                    $start = $month->copy()->startOfMonth()->toDateString();
                    $end = $month->copy()->endOfMonth()->toDateString();

                    return [
                        'label' => $month->translatedFormat('M Y'),
                        'hadir' => Presensi::query()
                            ->whereBetween('tanggal', [$start, $end])
                            ->where('status', 'hadir')
                            ->count(),
                        'telat' => Presensi::query()
                            ->whereBetween('tanggal', [$start, $end])
                            ->whereIn('status', ['telat', 'terlambat'])
                            ->count(),
                        'izin' => LeaveRequest::query()
                            ->where('status', 'approved')
                            ->whereDate('tanggal_mulai', '<=', $end)
                            ->whereDate('tanggal_selesai', '>=', $start)
                            ->count(),
                    ];
                })
                ->all(),
            default => collect(range(6, 0))
                ->map(function ($minusDay) {
                    $day = today()->subDays($minusDay)->toDateString();

                    return $this->chartPointForDay($day, Carbon::parse($day)->format('d/m'));
                })
                ->all(),
        };

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
            'totalShiftHariIni',
            'userMasukHariIni',
            'swapPending',
            'chart',
            'chartPeriod',
            'chartTitle',
            'presensis',
            'workSetting'
        ));
    }

    private function chartPointForDay(string $day, string $label): array
    {
        return [
            'label' => $label,
            'hadir' => Presensi::query()
                ->whereDate('tanggal', $day)
                ->where('status', 'hadir')
                ->count(),
            'telat' => Presensi::query()
                ->whereDate('tanggal', $day)
                ->whereIn('status', ['telat', 'terlambat'])
                ->count(),
            'izin' => LeaveRequest::query()
                ->where('status', 'approved')
                ->whereDate('tanggal_mulai', '<=', $day)
                ->whereDate('tanggal_selesai', '>=', $day)
                ->count(),
        ];
    }
}
