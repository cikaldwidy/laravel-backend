<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkAssignShiftRequest;
use App\Models\Announcement;
use App\Models\Department;
use App\Models\Shift;
use App\Models\ShiftSchedule;
use App\Models\ShiftSwap;
use App\Models\User;
use App\Support\ShiftTime;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShiftManagementController extends Controller
{
    public function schedules(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => ['nullable', 'date'],
            'unit_id' => ['nullable', 'integer', 'exists:departments,id'],
        ]);

        $tanggal = isset($validated['tanggal']) ? Carbon::parse($validated['tanggal']) : now();

        return redirect()->route('jadwal-dinas.index', array_filter([
            'bulan' => $tanggal->month,
            'tahun' => $tanggal->year,
            'unit_id' => $validated['unit_id'] ?? null,
        ]));
    }

    public function storeSchedule(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'tanggal' => ['required', 'date'],
            'status' => ['required', 'in:aktif,libur'],
            'shift_id' => ['nullable', 'integer', 'exists:shifts,id'],
            'jam_masuk' => ['nullable', 'date_format:H:i'],
            'jam_pulang' => ['nullable', 'date_format:H:i'],
        ]);

        $user = User::query()->where('id', $validated['user_id'])->where('role', 'user')->first();

        if (!$user) {
            return back()->withErrors(['user_id' => 'User tidak valid untuk assign shift.'])->withInput();
        }

        try {
            [$jamMasuk, $jamPulang] = $this->resolveShiftTimes(
                isset($validated['shift_id']) ? Shift::find($validated['shift_id']) : null,
                $validated['status'],
                $validated['jam_masuk'] ?? null,
                $validated['jam_pulang'] ?? null
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['jam_masuk' => $e->getMessage()])->withInput();
        }

        ShiftSchedule::updateOrCreate(
            [
                'user_id' => $validated['user_id'],
                'tanggal' => Carbon::parse($validated['tanggal'])->toDateString(),
            ],
            [
                'jam_masuk' => $jamMasuk,
                'jam_pulang' => $jamPulang,
                'status' => $validated['status'],
            ]
        );

        return back()->with('success', 'Jadwal shift berhasil disimpan.');
    }

    public function updateSchedule(Request $request, ShiftSchedule $shiftSchedule)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'tanggal' => ['required', 'date'],
            'status' => ['required', 'in:aktif,libur'],
            'shift_id' => ['nullable', 'integer', 'exists:shifts,id'],
            'jam_masuk' => ['nullable', 'date_format:H:i'],
            'jam_pulang' => ['nullable', 'date_format:H:i'],
        ]);

        $user = User::query()->where('id', $validated['user_id'])->where('role', 'user')->first();
        if (!$user) {
            return back()->withErrors(['user_id' => 'User tidak valid untuk assign shift.'])->withInput();
        }

        $tanggal = Carbon::parse($validated['tanggal'])->toDateString();

        $exists = ShiftSchedule::query()
            ->where('user_id', $validated['user_id'])
            ->whereDate('tanggal', $tanggal)
            ->where('id', '!=', $shiftSchedule->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['tanggal' => 'User sudah punya jadwal pada tanggal ini.'])->withInput();
        }

        try {
            [$jamMasuk, $jamPulang] = $this->resolveShiftTimes(
                isset($validated['shift_id']) ? Shift::find($validated['shift_id']) : null,
                $validated['status'],
                $validated['jam_masuk'] ?? null,
                $validated['jam_pulang'] ?? null
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['jam_masuk' => $e->getMessage()])->withInput();
        }

        $shiftSchedule->update([
            'user_id' => $validated['user_id'],
            'tanggal' => $tanggal,
            'jam_masuk' => $jamMasuk,
            'jam_pulang' => $jamPulang,
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Jadwal shift berhasil diperbarui.');
    }

    public function destroySchedule(ShiftSchedule $shiftSchedule)
    {
        $shiftSchedule->delete();

        return back()->with('success', 'Jadwal shift berhasil dihapus.');
    }

    public function bulkAssign(BulkAssignShiftRequest $request)
    {
        $data = $request->validated();

        $userIds = User::query()
            ->whereIn('id', $data['user_ids'])
            ->where('role', 'user')
            ->pluck('id')
            ->all();

        if (count($userIds) === 0) {
            return back()->withErrors(['user_ids' => 'Tidak ada user valid untuk di-assign.'])->withInput();
        }

        try {
            [$jamMasuk, $jamPulang] = $this->resolveShiftTimes(
                isset($data['shift_id']) ? Shift::find($data['shift_id']) : null,
                $data['status'],
                $data['jam_masuk'] ?? null,
                $data['jam_pulang'] ?? null
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['jam_masuk' => $e->getMessage()])->withInput();
        }

        $tanggal = Carbon::parse($data['tanggal'])->toDateString();

        DB::transaction(function () use ($userIds, $tanggal, $jamMasuk, $jamPulang, $data) {
            foreach ($userIds as $userId) {
                ShiftSchedule::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'tanggal' => $tanggal,
                    ],
                    [
                        'jam_masuk' => $jamMasuk,
                        'jam_pulang' => $jamPulang,
                        'status' => $data['status'],
                    ]
                );
            }
        });

        return back()->with('success', 'Bulk assign berhasil untuk ' . count($userIds) . ' user.');
    }

    public function downloadImportTemplate(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => ['required', 'integer', 'exists:departments,id'],
            'bulan_import' => ['required', 'date_format:Y-m'],
        ]);

        $unit = Department::query()->findOrFail($validated['unit_id']);
        $monthStart = Carbon::createFromFormat('Y-m-d', $validated['bulan_import'] . '-01')->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $users = User::query()
            ->where('role', 'user')
            ->whereHas('employeeDetail', fn ($detail) => $detail->where('department_id', $unit->id))
            ->orderBy('name')
            ->get();

        $safeUnitName = Str::slug($unit->nama_departemen ?: 'unit-kerja-bagian');
        $filename = 'template-jadwal-' . $safeUnitName . '-' . $monthStart->format('Y-m') . '.xlsx';
        $path = tempnam(sys_get_temp_dir(), 'jadwal-template-');

        $this->writeScheduleTemplateXlsx($path, $unit, $users, $monthStart, $monthEnd);

        return response()
            ->download($path, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->deleteFileAfterSend(true);
    }

    public function importUnitSchedules(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => ['required', 'integer', 'exists:departments,id'],
            'bulan_import' => ['required', 'date_format:Y-m'],
            'file' => ['required', 'file', 'max:4096', 'mimes:xlsx,csv,txt'],
        ]);

        $importMonth = Carbon::createFromFormat('Y-m-d', $validated['bulan_import'] . '-01')->startOfMonth();
        $importMonthEnd = $importMonth->copy()->endOfMonth();
        $periodLabel = $importMonth->translatedFormat('F Y');

        $users = User::query()
            ->with('employeeDetail')
            ->where('role', 'user')
            ->whereHas('employeeDetail', fn ($detail) => $detail->where('department_id', $validated['unit_id']))
            ->get();

        $usersByNip = $users
            ->filter(fn ($user) => filled($user->employeeDetail?->nip))
            ->keyBy(fn ($user) => Str::lower(trim($user->employeeDetail->nip)));
        $usersByEmail = $users
            ->filter(fn ($user) => filled($user->email))
            ->keyBy(fn ($user) => Str::lower(trim($user->email)));
        $usersByName = $users->keyBy(fn ($user) => Str::lower(trim($user->name)));

        $shifts = Shift::query()
            ->get()
            ->keyBy(fn ($shift) => Str::lower(trim($shift->nama_shift)));

        try {
            $rows = $this->readScheduleImportRows($validated['file'], $importMonth);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        if (count($rows) === 0) {
            return back()->with('error', 'File import tidak berisi data jadwal.');
        }

        $imported = 0;
        $skipped = 0;
        $errors = [];

        DB::transaction(function () use ($rows, $usersByNip, $usersByEmail, $usersByName, $shifts, $importMonth, $importMonthEnd, &$imported, &$skipped, &$errors) {
            foreach ($rows as $index => $row) {
                $line = $row['_source'] ?? ('Baris ' . ($index + 2));

                try {
                    $user = $this->findImportUser($row, $usersByNip, $usersByEmail, $usersByName);

                    if (!$user) {
                        throw new \InvalidArgumentException('pegawai tidak ditemukan di unit terpilih');
                    }

                    $tanggal = $this->normalizeImportDate($row['tanggal'] ?? null, null);
                    $tanggalCarbon = Carbon::parse($tanggal);

                    if ($tanggalCarbon->lt($importMonth) || $tanggalCarbon->gt($importMonthEnd)) {
                        throw new \InvalidArgumentException('tanggal harus berada pada bulan ' . $importMonth->translatedFormat('F Y'));
                    }

                    $shiftName = trim((string) ($row['shift'] ?? ''));
                    $status = Str::lower(trim((string) ($row['status'] ?? 'aktif'))) === 'libur' || $this->isImportOffCode($shiftName)
                        ? 'libur'
                        : 'aktif';
                    $shift = $this->findImportShift($shiftName, $shifts);

                    if ($shiftName !== '' && $status !== 'libur' && !$shift) {
                        throw new \InvalidArgumentException('template shift "' . $shiftName . '" tidak ditemukan');
                    }

                    [$jamMasuk, $jamPulang] = $this->resolveShiftTimes(
                        $shift,
                        $status,
                        $this->normalizeImportTime($row['jam_masuk'] ?? null),
                        $this->normalizeImportTime($row['jam_pulang'] ?? null)
                    );

                    ShiftSchedule::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'tanggal' => $tanggal,
                        ],
                        [
                            'jam_masuk' => $jamMasuk,
                            'jam_pulang' => $jamPulang,
                            'status' => $status,
                        ]
                    );

                    $imported++;
                } catch (\Throwable $e) {
                    $skipped++;
                    if (count($errors) < 8) {
                        $errors[] = $line . ': ' . $e->getMessage();
                    }
                }
            }
        });

        $message = 'Import jadwal ' . $periodLabel . ' selesai. ' . $imported . ' jadwal tersimpan';
        if ($skipped > 0) {
            $message .= ', ' . $skipped . ' baris dilewati. ' . implode(' | ', $errors);
            return back()->with('warning', $message);
        }

        return back()->with('success', $message . '.');
    }

    public function swaps(Request $request)
    {
        $status = $request->query('status');

        $query = ShiftSwap::query()
            ->with(['requester', 'targetUser', 'shift', 'targetShift', 'approver'])
            ->latest();

        if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $status);
        }

        $swaps = $query->paginate(15)->withQueryString();

        return view('admin.shift_management.swaps', compact('swaps', 'status'));
    }

    public function approveSwap(ShiftSwap $shiftSwap)
    {
        try {
            DB::transaction(function () use ($shiftSwap) {
                $swap = ShiftSwap::query()
                    ->whereKey($shiftSwap->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($swap->status !== 'pending') {
                    throw new \RuntimeException('Request swap sudah diproses sebelumnya.');
                }

                $shiftA = ShiftSchedule::query()
                    ->with('user.employeeDetail')
                    ->whereKey($swap->shift_id)
                    ->lockForUpdate()
                    ->first();
                $shiftB = ShiftSchedule::query()
                    ->with('user.employeeDetail')
                    ->whereKey($swap->target_shift_id)
                    ->lockForUpdate()
                    ->first();

                if (!$shiftA || !$shiftB) {
                    throw new \RuntimeException('Data shift tidak ditemukan.');
                }

                if (!$this->usersAreInSameUnit($shiftA->user, $shiftB->user)) {
                    throw new \RuntimeException('Swap shift hanya bisa disetujui untuk pegawai dalam unit yang sama.');
                }

                if ($shiftA->status !== 'aktif' || $shiftB->status !== 'aktif') {
                    throw new \RuntimeException('Hanya jadwal aktif yang bisa ditukar.');
                }

                if (!$this->shiftHasNotEnded($shiftA) || !$this->shiftHasNotEnded($shiftB)) {
                    throw new \RuntimeException('Shift yang sudah selesai tidak bisa ditukar.');
                }

                $this->applySwapSchedules($shiftA, $shiftB);

                $swap->update([
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);

                ShiftSwap::query()
                    ->where('status', 'pending')
                    ->where('id', '!=', $swap->id)
                    ->where(function ($q) use ($swap) {
                        $q->whereIn('shift_id', [$swap->shift_id, $swap->target_shift_id])
                            ->orWhereIn('target_shift_id', [$swap->shift_id, $swap->target_shift_id]);
                    })
                    ->update([
                        'status' => 'rejected',
                        'note' => 'Dibatalkan otomatis karena shift sudah diproses pada request lain.',
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                        'updated_at' => now(),
                    ]);
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->publishSwapDecisionAnnouncement($shiftSwap->fresh(['requester', 'targetUser', 'shift', 'targetShift']), 'approved');

        return back()->with('success', 'Swap shift berhasil di-approve dan kepemilikan shift sudah ditukar.');
    }

    public function rejectSwap(Request $request, ShiftSwap $shiftSwap)
    {
        $validated = $request->validate([
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($shiftSwap->status !== 'pending') {
            return back()->with('error', 'Request swap sudah diproses sebelumnya.');
        }

        $shiftSwap->update([
            'status' => 'rejected',
            'note' => $validated['note'] ?? $shiftSwap->note,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $this->publishSwapDecisionAnnouncement($shiftSwap->fresh(['requester', 'targetUser', 'shift', 'targetShift']), 'rejected', $validated['note'] ?? null);

        return back()->with('success', 'Request swap berhasil ditolak.');
    }

    private function resolveShiftTimes(?Shift $shift, string $status, ?string $jamMasuk, ?string $jamPulang): array
    {
        if ($status === 'libur') {
            return ['00:00:00', '00:00:00'];
        }

        if ($shift) {
            return [
                $this->toTimeString($shift->jam_masuk),
                $this->toTimeString($shift->jam_pulang),
            ];
        }

        if (!$jamMasuk || !$jamPulang) {
            throw new \InvalidArgumentException('Jam masuk dan jam pulang wajib diisi.');
        }

        return [$jamMasuk . ':00', $jamPulang . ':00'];
    }

    private function buildScheduleMatrix(Request $request, Carbon $selectedDate): array
    {
        $start = $selectedDate->copy()->startOfMonth();
        $end = $selectedDate->copy()->endOfMonth();
        $dates = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $dates[] = $cursor->copy();
            $cursor->addDay();
        }

        $users = User::query()
            ->with('employeeDetail.department')
            ->where('role', 'user')
            ->when($request->filled('unit_id'), function ($query) use ($request) {
                $query->whereHas('employeeDetail', fn ($detail) => $detail->where('department_id', $request->unit_id));
            }, function ($query) {
                $query->whereRaw('1 = 0');
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        $schedules = ShiftSchedule::query()
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->whereIn('user_id', $users->pluck('id'))
            ->get()
            ->keyBy(fn ($item) => $item->user_id . '|' . $item->tanggal->toDateString());

        $unitGroups = [];

        foreach ($users as $user) {
            $unitName = $user->employeeDetail?->department?->nama_departemen ?? $user->employeeDetail?->departemen ?? '-';
            $unitKey = Str::lower(trim($unitName));

            if (!isset($unitGroups[$unitKey])) {
                $unitGroups[$unitKey] = [
                    'unit' => $unitName,
                    'employees' => [],
                    'daily_totals' => $this->emptyScheduleDailyTotals($dates),
                    'total_masuk' => 0,
                    'total_libur' => 0,
                ];
            }

            $cells = [];
            $totalMasuk = 0;
            $totalLibur = 0;

            foreach ($dates as $date) {
                $dateKey = $date->toDateString();
                $schedule = $schedules->get($user->id . '|' . $dateKey);
                $cell = $this->makeScheduleMatrixCell($schedule);

                if ($cell['key'] === 'masuk') {
                    $totalMasuk++;
                    $unitGroups[$unitKey]['daily_totals'][$dateKey]['masuk']++;
                    $unitGroups[$unitKey]['total_masuk']++;
                }

                if ($cell['key'] === 'libur') {
                    $totalLibur++;
                    $unitGroups[$unitKey]['daily_totals'][$dateKey]['libur']++;
                    $unitGroups[$unitKey]['total_libur']++;
                }

                $cells[$dateKey] = $cell;
            }

            $unitGroups[$unitKey]['employees'][] = [
                'name' => $user->name,
                'cells' => $cells,
                'total_masuk' => $totalMasuk,
                'total_libur' => $totalLibur,
            ];
        }

        return [
            'dates' => $dates,
            'unit_groups' => array_values($unitGroups),
            'period_label' => $start->translatedFormat('F Y'),
        ];
    }

    private function makeScheduleMatrixCell(?ShiftSchedule $schedule): array
    {
        if (!$schedule) {
            return [
                'label' => '-',
                'class' => 'bg-slate-100 text-slate-400',
                'title' => 'Belum dijadwalkan',
                'key' => 'none',
            ];
        }

        if ($schedule->status === 'libur') {
            return [
                'label' => 'L',
                'class' => 'bg-red-600 text-white',
                'title' => 'Libur',
                'key' => 'libur',
            ];
        }

        return [
            'label' => 'M',
            'class' => 'bg-emerald-600 text-white',
            'title' => 'Masuk',
            'key' => 'masuk',
        ];
    }

    private function emptyScheduleDailyTotals(array $dates): array
    {
        $totals = [];

        foreach ($dates as $date) {
            $totals[$date->toDateString()] = [
                'masuk' => 0,
                'libur' => 0,
            ];
        }

        return $totals;
    }

    private function toTimeString($value): string
    {
        if ($value instanceof Carbon) {
            return $value->format('H:i:s');
        }

        if (is_string($value) && strlen($value) === 8) {
            return $value;
        }

        return Carbon::parse((string) $value)->format('H:i:s');
    }

    private function shiftHasNotEnded(ShiftSchedule $shift): bool
    {
        $end = ShiftTime::endAt(
            $shift->tanggal,
            $this->toTimeString($shift->jam_masuk),
            $this->toTimeString($shift->jam_pulang)
        );

        return !$end->isPast();
    }

    private function applySwapSchedules(ShiftSchedule $shiftA, ShiftSchedule $shiftB): void
    {
        $requesterId = (int) $shiftA->user_id;
        $targetId = (int) $shiftB->user_id;
        $dateA = $shiftA->tanggal->toDateString();
        $dateB = $shiftB->tanggal->toDateString();

        if ($dateA === $dateB) {
            $payloadA = $this->schedulePayload($shiftA);
            $payloadB = $this->schedulePayload($shiftB);

            $shiftA->update($payloadB);
            $shiftB->update($payloadA);
            return;
        }

        $relatedSchedules = ShiftSchedule::query()
            ->whereIn('user_id', [$requesterId, $targetId])
            ->whereIn('tanggal', [$dateA, $dateB])
            ->lockForUpdate()
            ->get()
            ->keyBy(fn (ShiftSchedule $schedule) => (int) $schedule->user_id . '|' . $schedule->tanggal->toDateString());

        $requesterOnTargetDate = $relatedSchedules->get($requesterId . '|' . $dateB);
        $targetOnRequesterDate = $relatedSchedules->get($targetId . '|' . $dateA);
        $payloadA = $this->schedulePayload($shiftA);
        $payloadB = $this->schedulePayload($shiftB);

        if ($targetOnRequesterDate) {
            $targetDatePayload = $this->schedulePayload($targetOnRequesterDate);
            $shiftA->update($targetDatePayload);
            $targetOnRequesterDate->update($payloadA);
        } else {
            $shiftA->update(['user_id' => $targetId]);
        }

        if ($requesterOnTargetDate) {
            $requesterDatePayload = $this->schedulePayload($requesterOnTargetDate);
            $shiftB->update($requesterDatePayload);
            $requesterOnTargetDate->update($payloadB);
        } else {
            $shiftB->update(['user_id' => $requesterId]);
        }
    }

    private function schedulePayload(ShiftSchedule $schedule): array
    {
        return [
            'jam_masuk' => $this->toTimeString($schedule->jam_masuk),
            'jam_pulang' => $this->toTimeString($schedule->jam_pulang),
            'status' => $schedule->status,
            'shift_code' => $schedule->shift_code,
        ];
    }

    private function publishSwapDecisionAnnouncement(?ShiftSwap $swap, string $decision, ?string $adminNote = null): void
    {
        if (!$swap) {
            return;
        }

        $requesterShift = $swap->shift
            ? $swap->shift->tanggal->format('d/m/Y') . ' ' . Carbon::parse($this->toTimeString($swap->shift->jam_masuk))->format('H:i') . ' - ' . Carbon::parse($this->toTimeString($swap->shift->jam_pulang))->format('H:i')
            : '-';
        $targetShift = $swap->targetShift
            ? $swap->targetShift->tanggal->format('d/m/Y') . ' ' . Carbon::parse($this->toTimeString($swap->targetShift->jam_masuk))->format('H:i') . ' - ' . Carbon::parse($this->toTimeString($swap->targetShift->jam_pulang))->format('H:i')
            : '-';
        $isApproved = $decision === 'approved';

        $message = trim(
            'Request tukar shift antara ' . ($swap->requester?->name ?? 'Pegawai') . ' dan ' . ($swap->targetUser?->name ?? 'pegawai target') . ' telah '
            . ($isApproved ? 'disetujui' : 'ditolak') . " oleh admin.\n\n"
            . 'Shift yang diajukan: ' . $requesterShift . "\n"
            . 'Shift target: ' . $targetShift
            . ($adminNote ? "\n\nCatatan admin: " . $adminNote : '')
        );

        $announcement = Announcement::query()->create([
            'judul' => $isApproved ? 'Tukar Shift Disetujui Admin' : 'Tukar Shift Ditolak Admin',
            'isi' => $message,
            'tanggal_mulai' => today()->toDateString(),
            'tanggal_berakhir' => today()->copy()->addDays(7)->toDateString(),
            'target_type' => 'users',
            'unit_id' => null,
            'is_published' => true,
        ]);

        $announcement->users()->sync([
            $swap->requester_id,
            $swap->target_user_id,
        ]);
    }

    private function usersAreInSameUnit(?User $firstUser, ?User $secondUser): bool
    {
        $firstUnitId = $firstUser?->employeeDetail?->department_id;
        $secondUnitId = $secondUser?->employeeDetail?->department_id;

        return $firstUnitId !== null && (int) $firstUnitId === (int) $secondUnitId;
    }

    private function readScheduleImportRows(UploadedFile $file, ?Carbon $importMonth = null): array
    {
        $extension = Str::lower($file->getClientOriginalExtension());
        $matrix = $extension === 'xlsx'
            ? $this->readXlsxRows($file->getRealPath())
            : $this->readCsvRows($file->getRealPath());

        $matrix = array_values(array_filter($matrix, fn ($row) => count(array_filter($row, fn ($value) => trim((string) $value) !== '')) > 0));

        if (count($matrix) < 2) {
            return [];
        }

        if ($importMonth) {
            $wideRows = $this->readWideScheduleImportRows($matrix, $importMonth);

            if ($wideRows !== null) {
                return $wideRows;
            }
        }

        $headers = array_map(fn ($header) => $this->normalizeImportHeader((string) $header), array_shift($matrix));
        $rows = [];

        foreach ($matrix as $row) {
            $assoc = [];

            foreach ($headers as $index => $header) {
                if ($header === '') {
                    continue;
                }

                $assoc[$header] = $row[$index] ?? null;
            }

            if (count(array_filter($assoc, fn ($value) => trim((string) $value) !== '')) > 0) {
                $assoc['_source'] = 'Baris ' . (count($rows) + 2);
                $rows[] = $assoc;
            }
        }

        return $rows;
    }

    private function writeScheduleTemplateXlsx(string $path, Department $unit, $users, Carbon $monthStart, Carbon $monthEnd): void
    {
        if (!class_exists(\ZipArchive::class)) {
            throw new \RuntimeException('Server belum mendukung ZipArchive untuk membuat file .xlsx.');
        }

        $zip = new \ZipArchive();

        if ($zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Template Excel tidak bisa dibuat.');
        }

        $zip->addFromString('[Content_Types].xml', $this->xlsxContentTypesXml());
        $zip->addFromString('_rels/.rels', $this->xlsxRootRelsXml());
        $zip->addFromString('xl/workbook.xml', $this->xlsxWorkbookXml($monthStart));
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->xlsxWorkbookRelsXml());
        $zip->addFromString('xl/styles.xml', $this->xlsxStylesXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->xlsxScheduleTemplateSheetXml($unit, $users, $monthStart, $monthEnd));
        $zip->close();
    }

    private function xlsxScheduleTemplateSheetXml(Unit $unit, $users, Carbon $monthStart, Carbon $monthEnd): string
    {
        $daysInMonth = $monthStart->daysInMonth;
        $lastColumn = $this->xlsxColumnName(1 + $daysInMonth);
        $firstScheduleCell = 'B9';
        $lastScheduleCell = $lastColumn . (max(9, 8 + $users->count()));
        $lastEmployeeRow = max(9, 8 + $users->count());
        $lastRow = $lastEmployeeRow + 6;
        $rows = [];

        $rows[] = $this->xlsxRow(1, [
            ['value' => 'JADWAL DINAS RS SATITI BULAN ' . Str::upper($monthStart->translatedFormat('F Y')), 'style' => 6],
        ]);
        $rows[] = $this->xlsxRow(2, [
            ['value' => 'Periode: ' . $monthStart->format('d/m/Y') . ' - ' . $monthEnd->format('d/m/Y'), 'style' => 6],
        ]);
        $rows[] = $this->xlsxRow(3, []);
        $rows[] = $this->xlsxRow(4, [
            ['value' => 'P', 'style' => 9],
            ['value' => 'Pagi', 'style' => 5],
            ['value' => 'S', 'style' => 10],
            ['value' => 'Sore', 'style' => 5],
            ['value' => 'M', 'style' => 11],
            ['value' => 'Malam', 'style' => 5],
            ['value' => 'O', 'style' => 12],
            ['value' => 'Libur', 'style' => 5],
        ]);
        $rows[] = $this->xlsxRow(5, [
            ['value' => 'Unit Kerja/Bagian ' . $unit->nama_departemen, 'style' => 6],
        ]);
        $rows[] = $this->xlsxRow(6, [
            ['value' => 'Pegawai: ' . $users->count(), 'style' => 6],
        ]);

        $dayNameCells = [
            ['value' => 'Nama / Tanggal', 'style' => 2],
        ];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $monthStart->copy()->day($day);
            $dayNameCells[] = [
                'value' => $date->translatedFormat('D'),
                'style' => $date->isWeekend() ? 4 : 3,
            ];
        }
        $rows[] = $this->xlsxRow(7, $dayNameCells);

        $dayNumberCells = [
            ['value' => '', 'style' => 2],
        ];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $monthStart->copy()->day($day);
            $dayNumberCells[] = [
                'value' => (string) $day,
                'style' => $date->isWeekend() ? 4 : 3,
            ];
        }
        $rows[] = $this->xlsxRow(8, $dayNumberCells);

        $rowNumber = 9;
        foreach ($users as $user) {
            $cells = [
                ['value' => $user->name, 'style' => 5],
            ];

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = $monthStart->copy()->day($day);
                $cells[] = ['value' => '', 'style' => $date->isWeekend() ? 8 : 0];
            }

            $rows[] = $this->xlsxRow($rowNumber, $cells);
            $rowNumber++;
        }

        $summaryStart = $rowNumber + 2;
        foreach (['Jumlah Grup Karyawan / Shift (hari)' => '*', 'Shift Pagi' => 'P', 'Shift Sore' => 'S', 'Shift Malam' => 'M', 'Libur' => 'O'] as $label => $code) {
            $cells = [['value' => $label, 'style' => 7]];

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $column = $this->xlsxColumnName(1 + $day);
                $formula = $code === '*'
                    ? 'COUNTA(' . $column . '9:' . $column . $lastEmployeeRow . ')'
                    : 'COUNTIF(' . $column . '9:' . $column . $lastEmployeeRow . ',"' . $code . '")';
                $cells[] = [
                    'formula' => $formula,
                    'style' => 7,
                ];
            }

            $rows[] = $this->xlsxRow($summaryStart, $cells);
            $summaryStart++;
        }

        $columns = [
            '<col min="1" max="1" width="34" customWidth="1"/>',
            '<col min="2" max="' . (1 + $daysInMonth) . '" width="4.2" customWidth="1"/>',
        ];

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<dimension ref="A1:' . $lastColumn . $lastRow . '"/>'
            . '<sheetViews><sheetView workbookViewId="0"><pane xSplit="1" ySplit="8" topLeftCell="B9" activePane="bottomRight" state="frozen"/><selection pane="bottomRight" activeCell="B9" sqref="B9"/></sheetView></sheetViews>'
            . '<cols>' . implode('', $columns) . '</cols>'
            . '<sheetData>' . implode('', $rows) . '</sheetData>'
            . '<mergeCells count="4"><mergeCell ref="A1:' . $lastColumn . '1"/><mergeCell ref="A2:' . $lastColumn . '2"/><mergeCell ref="A5:' . $lastColumn . '5"/><mergeCell ref="A7:A8"/></mergeCells>'
            . '<dataValidations count="1"><dataValidation type="list" allowBlank="1" showErrorMessage="1" errorTitle="Kode shift tidak valid" error="Gunakan kode P, S, M, O, atau kosongkan sel." sqref="' . $firstScheduleCell . ':' . $lastScheduleCell . '"><formula1>"P,S,M,O"</formula1></dataValidation></dataValidations>'
            . '<pageMargins left="0.25" right="0.25" top="0.5" bottom="0.5" header="0.2" footer="0.2"/>'
            . '<pageSetup paperSize="9" orientation="landscape" fitToWidth="1" fitToHeight="0"/>'
            . '</worksheet>';
    }

    private function xlsxRow(int $rowNumber, array $cells): string
    {
        $xml = '<row r="' . $rowNumber . '">';

        foreach ($cells as $index => $cell) {
            $reference = $this->xlsxColumnName($index + 1) . $rowNumber;
            $style = (int) ($cell['style'] ?? 0);

            if (array_key_exists('formula', $cell)) {
                $formula = $this->escapeXml((string) $cell['formula']);
                $xml .= '<c r="' . $reference . '" s="' . $style . '"><f>' . $formula . '</f><v>0</v></c>';
                continue;
            }

            $value = $this->escapeXml((string) ($cell['value'] ?? ''));
            $xml .= '<c r="' . $reference . '" s="' . $style . '" t="inlineStr"><is><t>' . $value . '</t></is></c>';
        }

        return $xml . '</row>';
    }

    private function xlsxColumnName(int $index): string
    {
        $name = '';

        while ($index > 0) {
            $index--;
            $name = chr(65 + ($index % 26)) . $name;
            $index = intdiv($index, 26);
        }

        return $name;
    }

    private function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    private function xlsxContentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';
    }

    private function xlsxRootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private function xlsxWorkbookXml(Carbon $monthStart): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="' . $this->escapeXml($monthStart->translatedFormat('F Y')) . '" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    private function xlsxWorkbookRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    private function xlsxStylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="4"><font><sz val="10"/><name val="Calibri"/></font><font><b/><sz val="13"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font><font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font><font><b/><sz val="10"/><name val="Calibri"/></font></fonts>'
            . '<fills count="11"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF0F766E"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFCCFBF1"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFFEE2E2"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFF8FAFC"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFE0F2FE"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FF22C55E"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFFACC15"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FF60A5FA"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFEF4444"/><bgColor indexed="64"/></patternFill></fill></fills>'
            . '<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left style="thin"><color rgb="FF94A3B8"/></left><right style="thin"><color rgb="FF94A3B8"/></right><top style="thin"><color rgb="FF94A3B8"/></top><bottom style="thin"><color rgb="FF94A3B8"/></bottom><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="13">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="2" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="4" borderId="1" xfId="0" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="2" fillId="7" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="3" fillId="8" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="2" fillId="9" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="2" fillId="10" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="5" borderId="1" xfId="0" applyFill="1" applyBorder="1"><alignment vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="6" borderId="1" xfId="0" applyFill="1" applyBorder="1"><alignment vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="4" borderId="1" xfId="0" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }

    private function readWideScheduleImportRows(array $matrix, Carbon $importMonth): ?array
    {
        $headerRowIndex = null;
        $dayHeaderRowIndex = null;
        $headers = [];

        foreach ($matrix as $index => $row) {
            $normalized = array_map(fn ($header) => $this->normalizeImportHeader((string) $header), $row);
            $hasName = in_array('nama', $normalized, true);
            $hasDayColumn = collect($row)->contains(fn ($value) => preg_match('/^\d{1,2}$/', trim((string) $value)) === 1);
            $nextRowHasDayColumn = isset($matrix[$index + 1])
                && collect($matrix[$index + 1])->contains(fn ($value) => preg_match('/^\d{1,2}$/', trim((string) $value)) === 1);

            if ($hasName && ($hasDayColumn || $nextRowHasDayColumn)) {
                $headerRowIndex = $index;
                $dayHeaderRowIndex = $hasDayColumn ? $index : $index + 1;
                $headers = $normalized;
                break;
            }
        }

        if ($headerRowIndex === null) {
            return null;
        }

        $identityColumns = [];
        $dayColumns = [];

        foreach ($headers as $index => $header) {
            if (in_array($header, ['nip', 'email', 'nama'], true)) {
                $identityColumns[$header] = $index;
                continue;
            }
        }

        foreach ($matrix[$dayHeaderRowIndex] ?? [] as $index => $value) {
            $rawHeader = trim((string) $value);
            if (preg_match('/^\d{1,2}$/', $rawHeader) === 1) {
                $day = (int) $rawHeader;

                if ($day >= 1 && $day <= $importMonth->daysInMonth) {
                    $dayColumns[$index] = $day;
                }
            }
        }

        if (!isset($identityColumns['nama']) || $dayColumns === []) {
            return null;
        }

        $rows = [];
        $dataRows = array_slice($matrix, $dayHeaderRowIndex + 1);

        foreach ($dataRows as $row) {
            $name = trim((string) ($row[$identityColumns['nama']] ?? ''));
            $nip = isset($identityColumns['nip']) ? trim((string) ($row[$identityColumns['nip']] ?? '')) : '';
            $email = isset($identityColumns['email']) ? trim((string) ($row[$identityColumns['email']] ?? '')) : '';

            if ($name === '' && $nip === '' && $email === '') {
                continue;
            }

            foreach ($dayColumns as $columnIndex => $day) {
                $shiftCode = trim((string) ($row[$columnIndex] ?? ''));

                if ($shiftCode === '') {
                    continue;
                }

                $rows[] = [
                    'nip' => $nip,
                    'email' => $email,
                    'nama' => $name,
                    'tanggal' => $importMonth->copy()->day($day)->toDateString(),
                    'status' => $this->isImportOffCode($shiftCode) ? 'libur' : 'aktif',
                    'shift' => $this->isImportOffCode($shiftCode) ? '' : $shiftCode,
                    'jam_masuk' => '',
                    'jam_pulang' => '',
                    '_source' => trim(($name !== '' ? $name : ($nip !== '' ? 'NIP ' . $nip : $email)) . ', tanggal ' . $importMonth->copy()->day($day)->format('d/m/Y')),
                ];
            }
        }

        return $rows;
    }

    private function readCsvRows(string $path): array
    {
        $handle = fopen($path, 'rb');

        if (!$handle) {
            throw new \RuntimeException('File CSV tidak bisa dibaca.');
        }

        $rows = [];
        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    private function readXlsxRows(string $path): array
    {
        if (!class_exists(\ZipArchive::class)) {
            throw new \RuntimeException('Server belum mendukung ZipArchive untuk membaca file .xlsx.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \RuntimeException('File .xlsx tidak bisa dibuka.');
        }

        $sharedStrings = $this->readXlsxSharedStrings($zip);
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            throw new \RuntimeException('Sheet pertama tidak ditemukan di file .xlsx.');
        }

        $sheet = simplexml_load_string($sheetXml);
        $rows = [];

        foreach ($sheet->sheetData->row as $sheetRow) {
            $row = [];

            foreach ($sheetRow->c as $cell) {
                $reference = (string) $cell['r'];
                $columnIndex = $this->xlsxColumnIndex($reference);
                $type = (string) $cell['t'];
                $value = isset($cell->v) ? (string) $cell->v : '';

                if ($type === 's') {
                    $value = $sharedStrings[(int) $value] ?? '';
                } elseif ($type === 'inlineStr') {
                    $value = (string) ($cell->is->t ?? '');
                }

                $row[$columnIndex] = $value;
            }

            if ($row !== []) {
                ksort($row);
                $normalizedRow = [];
                $lastColumn = max(array_keys($row));
                for ($column = 0; $column <= $lastColumn; $column++) {
                    $normalizedRow[] = $row[$column] ?? '';
                }
                $rows[] = $normalizedRow;
            }
        }

        return $rows;
    }

    private function readXlsxSharedStrings(\ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');

        if ($xml === false) {
            return [];
        }

        $shared = simplexml_load_string($xml);
        $strings = [];

        foreach ($shared->si as $item) {
            if (isset($item->t)) {
                $strings[] = (string) $item->t;
                continue;
            }

            $text = '';
            foreach ($item->r as $run) {
                $text .= (string) $run->t;
            }
            $strings[] = $text;
        }

        return $strings;
    }

    private function xlsxColumnIndex(string $reference): int
    {
        preg_match('/^[A-Z]+/i', $reference, $matches);
        $letters = strtoupper($matches[0] ?? 'A');
        $index = 0;

        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return $index - 1;
    }

    private function normalizeImportHeader(string $header): string
    {
        $key = Str::lower(trim($header));
        $key = str_replace([' ', '-', '.'], '_', $key);

        return match ($key) {
            'nama_pegawai', 'pegawai', 'user', 'nama_user' => 'nama',
            'nama/tanggal', 'nama_/_tanggal', 'nama_tanggal' => 'nama',
            'tanggal_shift', 'tgl', 'date' => 'tanggal',
            'nama_shift', 'template_shift', 'shift_id' => 'shift',
            'masuk', 'jam_masuk_manual' => 'jam_masuk',
            'pulang', 'jam_pulang_manual' => 'jam_pulang',
            default => $key,
        };
    }

    private function findImportUser(array $row, $usersByNip, $usersByEmail, $usersByName): ?User
    {
        $nip = Str::lower(trim((string) ($row['nip'] ?? '')));
        if ($nip !== '' && $usersByNip->has($nip)) {
            return $usersByNip->get($nip);
        }

        $email = Str::lower(trim((string) ($row['email'] ?? '')));
        if ($email !== '' && $usersByEmail->has($email)) {
            return $usersByEmail->get($email);
        }

        $name = Str::lower(trim((string) ($row['nama'] ?? '')));
        if ($name !== '' && $usersByName->has($name)) {
            return $usersByName->get($name);
        }

        return null;
    }

    private function findImportShift(string $shiftName, $shifts): ?Shift
    {
        $shiftName = trim($shiftName);

        if ($shiftName === '') {
            return null;
        }

        $key = Str::lower($shiftName);
        $alias = match ($key) {
            'p', 'pg', 'pagi' => 'pagi',
            's', 'sr', 'sore' => 'sore',
            'm', 'mlm', 'malam' => 'malam',
            default => null,
        };

        if ($shifts->has($key)) {
            return $shifts->get($key);
        }

        if (ctype_digit($shiftName)) {
            return Shift::query()->find($shiftName);
        }

        if ($alias) {
            $byName = $shifts->first(fn ($shift) => Str::contains(Str::lower($shift->nama_shift), $alias));

            if ($byName) {
                return $byName;
            }

            return $shifts->first(fn ($shift) => $this->shiftMatchesImportAlias($shift, $alias));
        }

        return null;
    }

    private function shiftMatchesImportAlias(Shift $shift, string $alias): bool
    {
        $hour = (int) Carbon::parse($this->toTimeString($shift->jam_masuk))->format('H');

        return match ($alias) {
            'pagi' => $hour >= 5 && $hour < 12,
            'sore' => $hour >= 12 && $hour < 20,
            'malam' => $hour >= 20 || $hour < 5,
            default => false,
        };
    }

    private function isImportOffCode(string $value): bool
    {
        return in_array(Str::lower(trim($value)), ['o', 'off', 'l', 'libur'], true);
    }

    private function normalizeImportDate($value, ?string $defaultDate): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            if ($defaultDate) {
                return $defaultDate;
            }

            throw new \InvalidArgumentException('tanggal wajib diisi untuk import bulanan');
        }

        if (is_numeric($value)) {
            return Carbon::create(1899, 12, 30)->addDays((int) $value)->toDateString();
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);

                if ($date && $date->format($format) === $value) {
                    return $date->toDateString();
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return Carbon::parse($value)->toDateString();
    }

    private function normalizeImportTime($value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $seconds = (int) round(((float) $value) * 86400);
            return gmdate('H:i', $seconds);
        }

        return Carbon::parse($value)->format('H:i');
    }
}
