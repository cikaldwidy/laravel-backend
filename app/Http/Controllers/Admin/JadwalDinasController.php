<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Shift;
use App\Models\ShiftSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JadwalDinasController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->buildScheduleData($request);

        return view('admin.jadwal_dinas.index', $data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bulan' => ['required', 'integer', 'between:1,12'],
            'tahun' => ['required', 'integer', 'between:2000,2100'],
            'unit_id' => ['required', 'integer', 'exists:departments,id'],
            'jadwal' => ['nullable', 'array'],
            'jadwal.*' => ['array'],
            'jadwal.*.*' => ['nullable', 'in:P,S,M,O'],
        ]);

        $monthStart = Carbon::create((int) $validated['tahun'], (int) $validated['bulan'], 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $employeeIds = User::query()
            ->where('role', 'user')
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        DB::transaction(function () use ($validated, $monthStart, $monthEnd, $employeeIds) {
            foreach ($validated['jadwal'] ?? [] as $userId => $dates) {
                if (!in_array((string) $userId, $employeeIds, true)) {
                    continue;
                }

                foreach ($dates as $date => $shiftCode) {
                    if (!$shiftCode) {
                        continue;
                    }

                    $tanggal = Carbon::parse($date);
                    if ($tanggal->lt($monthStart) || $tanggal->gt($monthEnd)) {
                        continue;
                    }

                    [$jamMasuk, $jamPulang, $status] = $this->resolveShiftPayload($shiftCode);

                    ShiftSchedule::updateOrCreate(
                        [
                            'user_id' => (int) $userId,
                            'tanggal' => $tanggal->toDateString(),
                        ],
                        [
                            'shift_code' => $shiftCode,
                            'jam_masuk' => $jamMasuk,
                            'jam_pulang' => $jamPulang,
                            'status' => $status,
                        ]
                    );
                }
            }
        });

        return redirect()
            ->route('jadwal-dinas.index', [
                'bulan' => $validated['bulan'],
                'tahun' => $validated['tahun'],
                'unit_id' => $validated['unit_id'],
            ])
            ->with('success', 'Jadwal dinas bulanan berhasil disimpan.');
    }

    public function export(Request $request): StreamedResponse
    {
        $request->validate([
            'unit_id' => ['required', 'integer', 'exists:departments,id'],
        ]);

        $data = $this->buildScheduleData($request);
        $filename = 'jadwal-dinas-' . $data['monthStart']->format('Y-m') . '.xls';

        return response()->streamDownload(function () use ($data) {
            echo view('admin.jadwal_dinas.export', $data)->render();
        }, $filename, ['Content-Type' => 'application/vnd.ms-excel; charset=UTF-8']);
    }

    private function buildScheduleData(Request $request): array
    {
        $validated = $request->validate([
            'bulan' => ['nullable', 'integer', 'between:1,12'],
            'tahun' => ['nullable', 'integer', 'between:2000,2100'],
            'unit_id' => ['nullable', 'integer', 'exists:departments,id'],
        ]);

        $bulan = (int) ($validated['bulan'] ?? now()->month);
        $tahun = (int) ($validated['tahun'] ?? now()->year);
        $selectedUnitId = $validated['unit_id'] ?? null;
        $selectedUnit = $selectedUnitId ? Department::query()->find($selectedUnitId) : null;
        $monthStart = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $dates = [];
        $cursor = $monthStart->copy();
        while ($cursor->lte($monthEnd)) {
            $dates[] = $cursor->copy();
            $cursor->addDay();
        }

        $employees = User::query()
            ->with('employeeDetail.department')
            ->where('role', 'user')
            ->when($selectedUnitId, function ($query) use ($selectedUnitId) {
                $query->whereHas('employeeDetail', fn ($detail) => $detail->where('department_id', $selectedUnitId));
            }, function ($query) {
                $query->whereRaw('1 = 0');
            })
            ->orderBy('name')
            ->get();

        $schedules = ShiftSchedule::query()
            ->whereIn('user_id', $employees->pluck('id'))
            ->whereBetween('tanggal', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get()
            ->keyBy(fn ($schedule) => $schedule->user_id . '|' . $schedule->tanggal->toDateString());

        $rows = $employees->map(function (User $employee) use ($dates, $schedules) {
            $cells = [];
            $totals = ['P' => 0, 'S' => 0, 'M' => 0, 'O' => 0];

            foreach ($dates as $date) {
                $key = $employee->id . '|' . $date->toDateString();
                $shiftCode = $this->scheduleCode($schedules->get($key));
                $cells[$date->toDateString()] = $shiftCode;

                if (isset($totals[$shiftCode])) {
                    $totals[$shiftCode]++;
                }
            }

            return [
                'employee' => $employee,
                'cells' => $cells,
                'totals' => $totals,
            ];
        });

        $unitGroups = $this->buildUnitGroups($rows, $dates);

        return [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'monthStart' => $monthStart,
            'dates' => $dates,
            'rows' => $rows,
            'unitGroups' => $unitGroups,
            'units' => Department::query()->orderBy('nama_departemen')->get(),
            'selectedUnitId' => $selectedUnitId,
            'selectedUnitName' => $selectedUnit?->nama_departemen ?? 'Pilih Unit Kerja/Bagian',
            'shiftOptions' => ['P' => 'Pagi', 'S' => 'Sore', 'M' => 'Malam', 'O' => 'Off'],
        ];
    }

    private function buildUnitGroups($rows, array $dates): array
    {
        $groups = [];

        foreach ($rows as $row) {
            $unitName = $row['employee']->employeeDetail?->department?->nama_departemen
                ?? $row['employee']->employeeDetail?->departemen
                ?? 'Tanpa Unit Kerja/Bagian';
            $unitKey = mb_strtolower(trim($unitName));

            if (!isset($groups[$unitKey])) {
                $groups[$unitKey] = [
                    'unit' => $unitName,
                    'employees' => [],
                    'daily_totals' => [],
                    'shift_totals' => ['P' => 0, 'S' => 0, 'M' => 0, 'O' => 0, 'TOTAL' => 0],
                ];

                foreach ($dates as $date) {
                    $groups[$unitKey]['daily_totals'][$date->toDateString()] = [
                        'P' => 0,
                        'S' => 0,
                        'M' => 0,
                        'O' => 0,
                        'TOTAL' => 0,
                    ];
                }
            }

            $groups[$unitKey]['employees'][] = $row;

            foreach ($dates as $date) {
                $dateKey = $date->toDateString();
                $code = $row['cells'][$dateKey] ?? '';

                if (isset($groups[$unitKey]['daily_totals'][$dateKey][$code])) {
                    $groups[$unitKey]['daily_totals'][$dateKey][$code]++;
                    $groups[$unitKey]['shift_totals'][$code]++;
                }

                if (in_array($code, ['P', 'S', 'M'], true)) {
                    $groups[$unitKey]['daily_totals'][$dateKey]['TOTAL']++;
                    $groups[$unitKey]['shift_totals']['TOTAL']++;
                }
            }
        }

        return array_values($groups);
    }

    private function scheduleCode(?ShiftSchedule $schedule): string
    {
        if (!$schedule) {
            return '';
        }

        if (in_array($schedule->shift_code, ['P', 'S', 'M', 'O'], true)) {
            return $schedule->shift_code;
        }

        if ($schedule->status === 'libur') {
            return 'O';
        }

        $hour = (int) $schedule->jam_masuk->format('H');
        if ($hour >= 5 && $hour < 12) {
            return 'P';
        }

        if ($hour >= 12 && $hour < 20) {
            return 'S';
        }

        return 'M';
    }

    private function resolveShiftPayload(string $shiftCode): array
    {
        if ($shiftCode === 'O') {
            return ['00:00:00', '00:00:00', 'libur'];
        }

        $shift = $this->findShiftTemplate($shiftCode);

        if ($shift) {
            return [
                $shift->jam_masuk,
                $shift->jam_pulang,
                'aktif',
            ];
        }

        return match ($shiftCode) {
            'P' => ['07:00:00', '14:00:00', 'aktif'],
            'S' => ['14:00:00', '21:00:00', 'aktif'],
            'M' => ['21:00:00', '07:00:00', 'aktif'],
        };
    }

    private function findShiftTemplate(string $shiftCode): ?Shift
    {
        $alias = match ($shiftCode) {
            'P' => 'pagi',
            'S' => 'sore',
            'M' => 'malam',
        };

        return Shift::query()
            ->get()
            ->first(function (Shift $shift) use ($alias) {
                $name = strtolower($shift->nama_shift);
                if (str_contains($name, $alias)) {
                    return true;
                }

                $hour = (int) Carbon::parse($shift->jam_masuk)->format('H');

                return match ($alias) {
                    'pagi' => $hour >= 5 && $hour < 12,
                    'sore' => $hour >= 12 && $hour < 20,
                    'malam' => $hour >= 20 || $hour < 5,
                };
            });
    }
}
