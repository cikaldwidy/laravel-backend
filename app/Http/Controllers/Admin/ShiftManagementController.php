<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkAssignShiftRequest;
use App\Models\Shift;
use App\Models\ShiftSchedule;
use App\Models\ShiftSwap;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShiftManagementController extends Controller
{
    public function schedules(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => ['nullable', 'date'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $tanggal = isset($validated['tanggal'])
            ? Carbon::parse($validated['tanggal'])->toDateString()
            : now()->toDateString();

        $query = ShiftSchedule::query()
            ->with('user')
            ->whereDate('tanggal', $tanggal)
            ->orderBy('jam_masuk')
            ->orderBy('id');

        if (!empty($validated['user_id'])) {
            $query->where('user_id', $validated['user_id']);
        }

        $schedules = $query->get();

        $users = User::query()
            ->where('role', 'user')
            ->orderBy('name')
            ->get(['id', 'name']);

        $shiftTemplates = Shift::query()
            ->orderBy('nama_shift')
            ->get();

        return view('admin.shift_management.schedules', compact('tanggal', 'schedules', 'users', 'shiftTemplates'));
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

                $shiftA = ShiftSchedule::query()->whereKey($swap->shift_id)->lockForUpdate()->first();
                $shiftB = ShiftSchedule::query()->whereKey($swap->target_shift_id)->lockForUpdate()->first();

                if (!$shiftA || !$shiftB) {
                    throw new \RuntimeException('Data shift tidak ditemukan.');
                }

                if ($shiftA->tanggal->toDateString() !== $shiftB->tanggal->toDateString()) {
                    throw new \RuntimeException('Shift harus berada pada tanggal yang sama.');
                }

                $startA = Carbon::parse($shiftA->tanggal->toDateString() . ' ' . $this->toTimeString($shiftA->jam_masuk));
                $startB = Carbon::parse($shiftB->tanggal->toDateString() . ' ' . $this->toTimeString($shiftB->jam_masuk));

                if ($startA->isPast() || $startB->isPast()) {
                    throw new \RuntimeException('Shift yang sudah lewat tidak bisa ditukar.');
                }

                DB::table('shift_schedules')
                    ->whereIn('id', [$shiftA->id, $shiftB->id])
                    ->update([
                        'user_id' => DB::raw('CASE WHEN id = ' . $shiftA->id . ' THEN ' . $shiftB->user_id . ' WHEN id = ' . $shiftB->id . ' THEN ' . $shiftA->user_id . ' END'),
                        'updated_at' => now(),
                    ]);

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
}
