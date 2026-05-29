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
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'tanggal' => ['nullable', 'date'],
            'chart_period' => ['nullable', 'in:7_days,1_month,1_year'],
            'latest_year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'latest_page' => ['nullable', 'integer', 'min:1'],
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

        $selectedYear = Carbon::parse($tanggal)->year;
        $yearStart = Carbon::create($selectedYear, 1, 1)->toDateString();
        $yearEnd = Carbon::create($selectedYear, 12, 31)->toDateString();
        $yearScheduled = ShiftSchedule::query()
            ->whereBetween('tanggal', [$yearStart, $yearEnd])
            ->where('status', 'aktif')
            ->count();
        $yearPresensi = Presensi::query()
            ->whereYear('tanggal', $selectedYear)
            ->count();
        $yearHadir = Presensi::query()
            ->whereYear('tanggal', $selectedYear)
            ->where('status', 'hadir')
            ->count();
        $yearTelat = Presensi::query()
            ->whereYear('tanggal', $selectedYear)
            ->whereIn('status', ['telat', 'terlambat'])
            ->count();
        $yearPulangCepat = Presensi::query()
            ->whereYear('tanggal', $selectedYear)
            ->where('status_pulang', 'pulang_cepat')
            ->count();
        $yearIzin = LeaveRequest::query()
            ->where('status', 'approved')
            ->whereDate('tanggal_mulai', '<=', $yearEnd)
            ->whereDate('tanggal_selesai', '>=', $yearStart)
            ->count();
        $yearAlpha = $this->alphaCountForRange($yearStart, $yearEnd);
        $yearKpiDenominator = max($yearScheduled, $yearPresensi, 1);
        $yearlyKpis = [
            'year' => $selectedYear,
            'target' => $yearKpiDenominator,
            'presensi' => $yearPresensi,
            'hadir' => $yearHadir,
            'telat' => $yearTelat,
            'pulangCepat' => $yearPulangCepat,
            'izin' => $yearIzin,
            'alpha' => $yearAlpha,
        ];

        $latestYear = (int) $request->input('latest_year', $selectedYear);
        $latestPage = (int) $request->input('latest_page', 1);
        $availableYears = Presensi::query()
            ->selectRaw('YEAR(tanggal) as year')
            ->whereNotNull('tanggal')
            ->groupBy('year')
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn ($year) => (int) $year)
            ->whenEmpty(fn ($years) => $years->push($selectedYear));

        $topOnTimeUsers = Presensi::query()
            ->selectRaw('user_id, COUNT(*) as total')
            ->with('user:id,name')
            ->whereYear('tanggal', $selectedYear)
            ->where('status', 'hadir')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $topLateUsers = Presensi::query()
            ->selectRaw('user_id, COUNT(*) as total')
            ->with('user:id,name')
            ->whereYear('tanggal', $selectedYear)
            ->whereIn('status', ['telat', 'terlambat'])
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $latestPresensiTotal = $this->latestPresensiActivityCount($latestYear);
        $latestPerPage = 5;
        $latestTotalPages = max((int) ceil($latestPresensiTotal / $latestPerPage), 1);
        $latestPage = min($latestPage, $latestTotalPages);
        $latestPresensiRows = $this->latestPresensiRows($latestYear, $latestPerPage, $latestPage);

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

        $userPulangHariIni = Presensi::query()
            ->whereDate('tanggal', $tanggal)
            ->whereNotNull('jam_keluar')
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
                        'pulangCepat' => Presensi::query()
                            ->whereBetween('tanggal', [$start, $end])
                            ->where('status_pulang', 'pulang_cepat')
                            ->count(),
                        'izin' => LeaveRequest::query()
                            ->where('status', 'approved')
                            ->whereDate('tanggal_mulai', '<=', $end)
                            ->whereDate('tanggal_selesai', '>=', $start)
                            ->count(),
                        'alpha' => $this->alphaCountForRange($start, $end),
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
            'userPulangHariIni',
            'swapPending',
            'chart',
            'chartPeriod',
            'chartTitle',
            'presensis',
            'workSetting',
            'selectedYear',
            'topOnTimeUsers',
            'topLateUsers',
            'latestPresensiRows',
            'latestYear',
            'availableYears',
            'latestPresensiTotal',
            'latestPerPage',
            'latestPage',
            'latestTotalPages',
            'yearlyKpis'
        ));
    }

    public function exportLatestPresensi(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
        ]);

        $year = (int) ($validated['year'] ?? now()->year);
        $rows = $this->latestPresensiRows($year);
        $filename = 'presensi-terbaru-' . $year . '.xls';

        return response()->streamDownload(function () use ($rows, $year) {
            echo '<html><head><meta charset="UTF-8"></head><body>';
            echo '<table border="1">';
            echo '<tr><th colspan="6">Presensi Terbaru ' . e((string) $year) . '</th></tr>';
            echo '<tr>';
            echo '<th>No</th>';
            echo '<th>Nama Pegawai</th>';
            echo '<th>Tanggal</th>';
            echo '<th>Waktu</th>';
            echo '<th>Jenis</th>';
            echo '<th>Status</th>';
            echo '</tr>';

            foreach ($rows->values() as $index => $row) {
                echo '<tr>';
                echo '<td>' . ($index + 1) . '</td>';
                echo '<td>' . e($row['user']?->name ?? 'User dihapus') . '</td>';
                echo '<td>' . e($row['tanggal']->format('Y-m-d')) . '</td>';
                echo '<td>' . e($row['waktu']->format('H:i:s')) . '</td>';
                echo '<td>' . e($row['jenis']) . '</td>';
                echo '<td>' . e($row['label']) . '</td>';
                echo '</tr>';
            }

            echo '</table>';
            echo '</body></html>';
        }, $filename, ['Content-Type' => 'application/vnd.ms-excel; charset=UTF-8']);
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
            'pulangCepat' => Presensi::query()
                ->whereDate('tanggal', $day)
                ->where('status_pulang', 'pulang_cepat')
                ->count(),
            'izin' => LeaveRequest::query()
                ->where('status', 'approved')
                ->whereDate('tanggal_mulai', '<=', $day)
                ->whereDate('tanggal_selesai', '>=', $day)
                ->count(),
            'alpha' => $this->alphaCountForDay($day),
        ];
    }

    private function alphaCountForDay(string $day): int
    {
        $scheduledUserIds = ShiftSchedule::query()
            ->whereDate('tanggal', $day)
            ->where('status', 'aktif')
            ->pluck('user_id')
            ->unique();

        if ($scheduledUserIds->isEmpty()) {
            return 0;
        }

        $presentUserIds = Presensi::query()
            ->whereDate('tanggal', $day)
            ->pluck('user_id')
            ->unique();

        $leaveUserIds = LeaveRequest::query()
            ->where('status', 'approved')
            ->whereDate('tanggal_mulai', '<=', $day)
            ->whereDate('tanggal_selesai', '>=', $day)
            ->pluck('user_id')
            ->unique();

        return $scheduledUserIds
            ->diff($presentUserIds)
            ->diff($leaveUserIds)
            ->count();
    }

    private function alphaCountForRange(string $start, string $end): int
    {
        return collect(Carbon::parse($start)->daysUntil(Carbon::parse($end)->addDay()))
            ->sum(fn (Carbon $day) => $this->alphaCountForDay($day->toDateString()));
    }

    private function latestPresensiRows(int $year, ?int $limit = null, int $page = 1)
    {
        $rows = Presensi::with('user:id,name')
            ->whereYear('tanggal', $year)
            ->orderByDesc('tanggal')
            ->orderByDesc('updated_at')
            ->get()
            ->flatMap(function (Presensi $presensi) {
                return collect([
                    $presensi->jam_keluar ? $this->presensiActivityRow($presensi, 'pulang', $presensi->jam_keluar, $presensi->status_pulang ?: 'normal') : null,
                    $presensi->jam_masuk ? $this->presensiActivityRow($presensi, 'masuk', $presensi->jam_masuk, $presensi->status ?: 'normal') : null,
                ])->filter();
            })
            ->map(function (array $row) {
                $row['label'] = $this->presensiStatusLabel($row['status']);

                return $row;
            })
            ->sortByDesc(fn (array $row) => $row['tanggal']->format('Y-m-d') . ' ' . $row['waktu']->format('H:i:s'))
            ->values();

        return $limit ? $rows->forPage($page, $limit)->values() : $rows;
    }

    private function latestPresensiActivityCount(int $year): int
    {
        $totals = Presensi::query()
            ->whereYear('tanggal', $year)
            ->selectRaw('
                SUM(CASE WHEN jam_masuk IS NOT NULL THEN 1 ELSE 0 END) as masuk_total,
                SUM(CASE WHEN jam_keluar IS NOT NULL THEN 1 ELSE 0 END) as pulang_total
            ')
            ->first();

        return (int) ($totals?->masuk_total ?? 0) + (int) ($totals?->pulang_total ?? 0);
    }

    private function presensiActivityRow(Presensi $presensi, string $jenis, Carbon $waktu, string $status): array
    {
        return [
            'user' => $presensi->user,
            'tanggal' => $presensi->tanggal,
            'waktu' => $waktu,
            'jenis' => $jenis,
            'status' => $status,
        ];
    }

    private function presensiStatusLabel(string $status): string
    {
        return match ($status) {
            'hadir', 'normal' => 'tepat waktu',
            'telat', 'terlambat' => 'telat',
            'pulang_cepat' => 'pulang cepat',
            default => str_replace('_', ' ', $status),
        };
    }
}
