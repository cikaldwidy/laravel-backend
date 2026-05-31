<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\Presensi;
use App\Models\ShiftSchedule;
use App\Models\User;
use App\Support\ShiftTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        [$tanggalMulai, $tanggalSelesai] = $this->resolveDateRange($request);
        $rows = $this->buildRows($request, $tanggalMulai, $tanggalSelesai);
        $matrix = $this->buildMatrixReport($request, $tanggalMulai, $tanggalSelesai);
        $units = Department::query()->orderBy('nama_departemen')->get();
        $selectedMonth = $tanggalMulai->format('Y-m');

        return view('admin.reports.index', compact('rows', 'matrix', 'units', 'tanggalMulai', 'tanggalSelesai', 'selectedMonth'));
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $request->validate([
            'unit_id' => ['required', 'integer', 'exists:departments,id'],
        ]);

        [$tanggalMulai, $tanggalSelesai] = $this->resolveDateRange($request);
        $matrix = $this->buildMatrixReport($request, $tanggalMulai, $tanggalSelesai);
        $filename = 'laporan-presensi-' . $tanggalMulai->format('Ymd') . '-' . $tanggalSelesai->format('Ymd') . '.xls';

        return response()->streamDownload(function () use ($matrix, $tanggalMulai, $tanggalSelesai) {
            echo view('admin.reports.excel', compact('matrix', 'tanggalMulai', 'tanggalSelesai'))->render();
        }, $filename, ['Content-Type' => 'application/vnd.ms-excel; charset=UTF-8']);
    }

    public function exportPdf(Request $request)
    {
        $request->validate([
            'unit_id' => ['required', 'integer', 'exists:departments,id'],
        ]);

        [$tanggalMulai, $tanggalSelesai] = $this->resolveDateRange($request);
        $matrix = $this->buildMatrixReport($request, $tanggalMulai, $tanggalSelesai);

        return view('admin.reports.pdf', compact('matrix', 'tanggalMulai', 'tanggalSelesai'));
    }

    private function resolveDateRange(Request $request): array
    {
        $validated = $request->validate([
            'bulan' => ['nullable', 'date_format:Y-m'],
            'unit_id' => ['nullable', 'integer', 'exists:departments,id'],
        ]);

        $month = isset($validated['bulan'])
            ? Carbon::createFromFormat('Y-m', $validated['bulan'])
            : today();

        return [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()];
    }

    private function buildRows(Request $request, Carbon $tanggalMulai, Carbon $tanggalSelesai): array
    {
        $users = User::query()
            ->with(['employeeDetail.department', 'leaveRequests'])
            ->where('role', 'user')
            ->when($request->filled('unit_id'), function ($query) use ($request) {
                $query->whereHas('employeeDetail', fn ($detail) => $detail->where('department_id', $request->unit_id));
            }, function ($query) {
                $query->whereRaw('1 = 0');
            })
            ->get()
            ->keyBy('id');

        $presensis = Presensi::query()
            ->whereBetween('tanggal', [$tanggalMulai->toDateString(), $tanggalSelesai->toDateString()])
            ->whereIn('user_id', $users->keys())
            ->get()
            ->keyBy(fn ($item) => $item->user_id . '|' . $item->tanggal->toDateString());

        $shifts = ShiftSchedule::query()
            ->whereBetween('tanggal', [$tanggalMulai->toDateString(), $tanggalSelesai->toDateString()])
            ->whereIn('user_id', $users->keys())
            ->get()
            ->keyBy(fn ($item) => $item->user_id . '|' . $item->tanggal->toDateString());

        $approvedLeaves = LeaveRequest::query()
            ->where('status', 'approved')
            ->whereIn('user_id', $users->keys())
            ->whereDate('tanggal_mulai', '<=', $tanggalSelesai->toDateString())
            ->whereDate('tanggal_selesai', '>=', $tanggalMulai->toDateString())
            ->get();

        $overtimes = $this->overtimeMap($users->keys(), $tanggalMulai, $tanggalSelesai);

        $leaveMap = [];
        foreach ($approvedLeaves as $leave) {
            $cursor = $leave->tanggal_mulai->copy();
            while ($cursor->lte($leave->tanggal_selesai)) {
                $leaveMap[$leave->user_id . '|' . $cursor->toDateString()] = $leave;
                $cursor->addDay();
            }
        }

        $rows = [];
        $cursor = $tanggalMulai->copy()->startOfDay();
        while ($cursor->lte($tanggalSelesai)) {
            foreach ($users as $user) {
                $key = $user->id . '|' . $cursor->toDateString();
                $presensi = $presensis->get($key);
                $shift = $shifts->get($key);
                $leave = $leaveMap[$key] ?? null;
                $overtime = $overtimes[$key] ?? null;

                if (!$shift && !$presensi && !$leave && !$overtime) {
                    continue;
                }

                $status = $leave ? $leave->jenis_izin : ($presensi?->status ?? ($overtime ? 'lembur' : 'alpha'));
                $rows[] = [
                    'tanggal' => $cursor->format('Y-m-d'),
                    'nama' => $user->name,
                    'unit' => $user->employeeDetail?->department?->nama_departemen ?? $user->employeeDetail?->departemen ?? '-',
                    'shift' => $shift
                        ? ($shift->nama_shift . ' (' . $shift->jam_masuk->format('H:i') . '-' . $shift->jam_pulang->format('H:i') . ')')
                        : ($overtime ? ('Lembur (' . $overtime->jam_mulai?->format('H:i') . '-' . $overtime->jam_selesai?->format('H:i') . ')') : '-'),
                    'check_in' => $presensi?->jam_masuk?->format('H:i') ?? '-',
                    'check_out' => $presensi?->jam_keluar?->format('H:i') ?? '-',
                    'status' => $status,
                    'keterangan' => $overtime
                        ? 'Lembur pengganti sakit: ' . $this->compensationLabel($overtime->compensation_type)
                        : ($leave?->jenis_izin ?? ($presensi?->status_pulang ?? '-')),
                ];
            }
            $cursor->addDay();
        }

        return $rows;
    }

    private function buildMatrixReport(Request $request, Carbon $tanggalMulai, Carbon $tanggalSelesai): array
    {
        $users = User::query()
            ->with(['employeeDetail.department'])
            ->where('role', 'user')
            ->when($request->filled('unit_id'), function ($query) use ($request) {
                $query->whereHas('employeeDetail', fn ($detail) => $detail->where('department_id', $request->unit_id));
            }, function ($query) {
                $query->whereRaw('1 = 0');
            })
            ->orderBy('name')
            ->get();

        $userIds = $users->pluck('id');
        $dates = [];
        $cursor = $tanggalMulai->copy()->startOfDay();
        while ($cursor->lte($tanggalSelesai)) {
            $dates[] = $cursor->copy();
            $cursor->addDay();
        }

        $presensis = Presensi::query()
            ->whereBetween('tanggal', [$tanggalMulai->toDateString(), $tanggalSelesai->toDateString()])
            ->whereIn('user_id', $userIds)
            ->get()
            ->keyBy(fn ($item) => $item->user_id . '|' . $item->tanggal->toDateString());

        $shifts = ShiftSchedule::query()
            ->whereBetween('tanggal', [$tanggalMulai->toDateString(), $tanggalSelesai->toDateString()])
            ->whereIn('user_id', $userIds)
            ->get()
            ->keyBy(fn ($item) => $item->user_id . '|' . $item->tanggal->toDateString());

        $approvedLeaves = LeaveRequest::query()
            ->where('status', 'approved')
            ->whereIn('user_id', $userIds)
            ->whereDate('tanggal_mulai', '<=', $tanggalSelesai->toDateString())
            ->whereDate('tanggal_selesai', '>=', $tanggalMulai->toDateString())
            ->get();

        $overtimes = $this->overtimeMap($userIds, $tanggalMulai, $tanggalSelesai);

        $leaveMap = [];
        foreach ($approvedLeaves as $leave) {
            $leaveCursor = $leave->tanggal_mulai->copy();
            while ($leaveCursor->lte($leave->tanggal_selesai)) {
                $leaveMap[$leave->user_id . '|' . $leaveCursor->toDateString()] = $leave;
                $leaveCursor->addDay();
            }
        }

        $dailyTotals = [];
        foreach ($dates as $date) {
            $dailyTotals[$date->toDateString()] = [
                'pagi' => 0,
                'sore' => 0,
                'malam' => 0,
                'masuk' => 0,
                'terlambat' => 0,
                'belum_pulang' => 0,
                'belum_masuk' => 0,
                'libur' => 0,
                'izin' => 0,
                'menunggu' => 0,
            ];
        }

        $employeeRows = [];

        foreach ($users as $user) {
            $cells = [];
            $dailyShiftGroups = [];
            $shiftTotals = ['pagi' => 0, 'sore' => 0, 'malam' => 0];
            $totalMinutes = 0;
            $unitName = $user->employeeDetail?->department?->nama_departemen ?? $user->employeeDetail?->departemen ?? '-';

            foreach ($dates as $date) {
                $dateKey = $date->toDateString();
                $key = $user->id . '|' . $dateKey;
                $shift = $shifts->get($key);
                $presensi = $presensis->get($key);
                $leave = $leaveMap[$key] ?? null;
                $overtime = $overtimes[$key] ?? null;
                $cell = $this->makeMatrixCell($shift, $presensi, $leave, $overtime);
                $shiftGroup = null;

                if ($shift && $shift->status === 'aktif') {
                    $shiftGroup = $this->shiftGroup($shift);
                    $shiftTotals[$shiftGroup]++;
                    $dailyTotals[$dateKey][$shiftGroup]++;
                } elseif ($overtime?->jam_mulai) {
                    $shiftGroup = $this->shiftGroupFromHour((int) $overtime->jam_mulai->format('H'));
                }

                if ($presensi?->jam_masuk && $presensi?->jam_keluar) {
                    $totalMinutes += $this->presensiMinutes($presensi);
                }

                $dailyShiftGroups[$dateKey] = $shiftGroup ?? null;
                $dailyTotals[$dateKey][$cell['status_key']]++;
                $cells[$dateKey] = $cell;
            }

            $employeeRows[] = [
                'name' => $user->name,
                'unit' => $unitName,
                'unit_key' => $this->unitReportKey($unitName),
                'cells' => $cells,
                'daily_shift_groups' => $dailyShiftGroups,
                'shift_totals' => $shiftTotals,
                'total_hours' => round($totalMinutes / 60, 1),
            ];
        }

        $unitGroups = [];
        foreach ($employeeRows as $employee) {
            $unitKey = $employee['unit_key'];

            if (!isset($unitGroups[$unitKey])) {
                $unitGroups[$unitKey] = [
                    'unit' => $employee['unit'],
                    'employees' => [],
                    'daily_totals' => $this->emptyDailyTotals($dates),
                    'shift_totals' => ['pagi' => 0, 'sore' => 0, 'malam' => 0],
                    'total_hours' => 0,
                ];
            }

            $unitGroups[$unitKey]['employees'][] = $employee;
            $unitGroups[$unitKey]['total_hours'] += $employee['total_hours'];

            foreach (['pagi', 'sore', 'malam'] as $shiftKey) {
                $unitGroups[$unitKey]['shift_totals'][$shiftKey] += $employee['shift_totals'][$shiftKey];
            }

            foreach ($dates as $date) {
                $dateKey = $date->toDateString();
                $shiftGroup = $employee['daily_shift_groups'][$dateKey] ?? null;

                if ($shiftGroup) {
                    $unitGroups[$unitKey]['daily_totals'][$dateKey][$shiftGroup]++;
                }
            }
        }

        return [
            'dates' => $dates,
            'employees' => $employeeRows,
            'unit_groups' => array_values($unitGroups),
            'daily_totals' => $dailyTotals,
            'legend' => [
                ['label' => 'M', 'text' => 'Sudah absen masuk / lengkap', 'class' => 'cell-present'],
                ['label' => 'T', 'text' => 'Terlambat', 'class' => 'cell-late'],
                ['label' => 'BP', 'text' => 'Belum absen pulang', 'class' => 'cell-warning'],
                ['label' => 'BM', 'text' => 'Belum absen masuk / alpha', 'class' => 'cell-danger'],
                ['label' => 'L', 'text' => 'Libur', 'class' => 'cell-off'],
                ['label' => 'I', 'text' => 'Izin approved', 'class' => 'cell-leave'],
                ['label' => 'S', 'text' => 'Sakit approved', 'class' => 'cell-sick'],
                ['label' => 'LB', 'text' => 'Lembur pengganti sakit', 'class' => 'cell-overtime'],
            ],
        ];
    }

    private function makeMatrixCell(?ShiftSchedule $shift, ?Presensi $presensi, ?LeaveRequest $leave, ?OvertimeRequest $overtime = null): array
    {
        if ($leave) {
            if ($leave->jenis_izin === 'sakit') {
                return [
                    'label' => 'S',
                    'title' => 'Sakit approved',
                    'class' => 'cell-sick',
                    'status_key' => 'izin',
                ];
            }

            return [
                'label' => 'I',
                'title' => 'Izin: ' . $leave->jenis_izin,
                'class' => 'cell-leave',
                'status_key' => 'izin',
            ];
        }

        if ($shift && $shift->status === 'libur') {
            return [
                'label' => 'L',
                'title' => 'Libur',
                'class' => 'cell-off',
                'status_key' => 'libur',
            ];
        }

        if (!$shift && !$overtime) {
            return [
                'label' => '-',
                'title' => 'Tidak ada jadwal',
                'class' => 'cell-empty',
                'status_key' => 'libur',
            ];
        }

        $shiftDate = $shift?->tanggal ?? $overtime?->tanggal_mulai;
        $jamMasuk = $shift?->jam_masuk?->format('H:i:s') ?? $overtime?->jam_mulai?->format('H:i:s');
        $jamPulang = $shift?->jam_pulang?->format('H:i:s') ?? $overtime?->jam_selesai?->format('H:i:s');
        if (!$jamMasuk || !$jamPulang) {
            return [
                'label' => 'LB',
                'title' => 'Lembur pengganti sakit',
                'class' => 'cell-overtime',
                'status_key' => 'menunggu',
            ];
        }

        $shiftStart = ShiftTime::startAt($shiftDate, $jamMasuk);
        $shiftEnd = ShiftTime::endAt($shiftDate, $jamMasuk, $jamPulang);
        $now = now();
        $titleSuffix = $overtime ? ' + lembur pengganti sakit (' . $this->compensationLabel($overtime->compensation_type) . ')' : '';

        if (!$presensi || !$presensi->jam_masuk) {
            if ($now->lt($shiftStart)) {
                return [
                    'label' => $overtime ? 'LB' : '-',
                    'title' => ($overtime ? 'Lembur pengganti sakit belum mulai' : 'Jadwal belum mulai'),
                    'class' => $overtime ? 'cell-overtime' : 'cell-empty',
                    'status_key' => 'menunggu',
                ];
            }

            return [
                'label' => 'BM',
                'title' => 'Belum absen masuk' . $titleSuffix,
                'class' => 'cell-danger',
                'status_key' => 'belum_masuk',
            ];
        }

        $isLate = in_array($presensi->status, ['telat', 'terlambat'], true);
        $hasCheckout = (bool) $presensi->jam_keluar;

        if ($isLate && !$hasCheckout) {
            if ($now->lt($shiftEnd)) {
                return [
                    'label' => 'T',
                    'title' => 'Terlambat, belum waktunya pulang' . $titleSuffix,
                    'class' => 'cell-late',
                    'status_key' => 'terlambat',
                ];
            }

            return [
                'label' => 'T/BP',
                'title' => 'Terlambat, belum absen pulang' . $titleSuffix,
                'class' => 'cell-late',
                'status_key' => 'terlambat',
            ];
        }

        if ($isLate) {
            return [
                'label' => 'T',
                'title' => 'Terlambat' . $titleSuffix,
                'class' => 'cell-late',
                'status_key' => 'terlambat',
            ];
        }

        if (!$hasCheckout) {
            if ($now->lt($shiftEnd)) {
                return [
                    'label' => 'M',
                    'title' => 'Sudah absen masuk, belum waktunya pulang' . $titleSuffix,
                    'class' => 'cell-present',
                    'status_key' => 'masuk',
                ];
            }

            return [
                'label' => 'BP',
                'title' => 'Belum absen pulang' . $titleSuffix,
                'class' => 'cell-warning',
                'status_key' => 'belum_pulang',
            ];
        }

        return [
            'label' => $overtime ? 'M+LB' : 'M',
            'title' => 'Masuk lengkap' . $titleSuffix,
            'class' => $overtime ? 'cell-overtime' : 'cell-present',
            'status_key' => 'masuk',
        ];
    }

    private function shiftGroup(ShiftSchedule $shift): string
    {
        $hour = (int) $shift->jam_masuk->format('H');

        if ($hour >= 5 && $hour < 12) {
            return 'pagi';
        }

        if ($hour >= 12 && $hour < 18) {
            return 'sore';
        }

        return 'malam';
    }

    private function shiftGroupFromHour(int $hour): string
    {
        if ($hour >= 5 && $hour < 12) {
            return 'pagi';
        }

        if ($hour >= 12 && $hour < 18) {
            return 'sore';
        }

        return 'malam';
    }

    private function overtimeMap($userIds, Carbon $tanggalMulai, Carbon $tanggalSelesai): array
    {
        $overtimeMap = [];
        $overtimes = OvertimeRequest::query()
            ->where('status', 'approved')
            ->whereIn('user_id', $userIds)
            ->whereDate('tanggal_mulai', '<=', $tanggalSelesai->toDateString())
            ->whereDate('tanggal_selesai', '>=', $tanggalMulai->toDateString())
            ->get();

        foreach ($overtimes as $overtime) {
            $cursor = $overtime->tanggal_mulai->copy()->startOfDay();
            while ($cursor->lte($overtime->tanggal_selesai)) {
                if ($cursor->gte($tanggalMulai) && $cursor->lte($tanggalSelesai)) {
                    $overtimeMap[$overtime->user_id . '|' . $cursor->toDateString()] = $overtime;
                }
                $cursor->addDay();
            }
        }

        return $overtimeMap;
    }

    private function compensationLabel(?string $type): string
    {
        return match ($type) {
            'libur_pengganti' => 'Libur Pengganti',
            default => 'Uang Lembur',
        };
    }

    private function presensiMinutes(Presensi $presensi): int
    {
        $start = Carbon::parse($presensi->tanggal->toDateString() . ' ' . $presensi->jam_masuk->format('H:i:s'));
        $end = Carbon::parse($presensi->tanggal->toDateString() . ' ' . $presensi->jam_keluar->format('H:i:s'));

        if ($end->lte($start)) {
            $end->addDay();
        }

        return $start->diffInMinutes($end);
    }

    private function emptyDailyTotals(array $dates): array
    {
        $totals = [];

        foreach ($dates as $date) {
            $totals[$date->toDateString()] = [
                'pagi' => 0,
                'sore' => 0,
                'malam' => 0,
            ];
        }

        return $totals;
    }

    private function unitReportKey(string $unitName): string
    {
        return mb_strtolower(trim($unitName !== '' ? $unitName : '-'));
    }
}
