<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\ShiftSchedule;
use App\Models\ShiftSwap;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShiftSwapController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $roleResponse = $this->ensureUser($user);
        if ($roleResponse) {
            return $roleResponse;
        }

        $status = $request->query('status');

        $items = ShiftSwap::query()
            ->with(['requester.employeeDetail.unit', 'targetUser.employeeDetail.unit', 'shift', 'targetShift', 'approver'])
            ->where(function ($query) use ($user) {
                $query->where('requester_id', $user->id)
                    ->orWhere('target_user_id', $user->id);
            })
            ->when(in_array($status, ['pending', 'approved', 'rejected'], true), fn ($query) => $query->where('status', $status))
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn (ShiftSwap $item) => $this->formatSwap($item, $user->id))
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Daftar tukar shift berhasil diambil',
            'data' => $items,
        ]);
    }

    public function options(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user()->loadMissing('employeeDetail.unit');
        $roleResponse = $this->ensureUser($user);
        if ($roleResponse) {
            return $roleResponse;
        }

        $unitId = $user->employeeDetail?->unit_id;

        $myShifts = ShiftSchedule::query()
            ->where('user_id', $user->id)
            ->whereDate('tanggal', '>=', today())
            ->where('status', 'aktif')
            ->orderBy('tanggal')
            ->orderBy('jam_masuk')
            ->get()
            ->map(fn (ShiftSchedule $item) => $this->formatShift($item))
            ->values();

        $users = User::query()
            ->where('role', 'user')
            ->where('id', '!=', $user->id)
            ->when($unitId, function ($query) use ($unitId) {
                $query->whereHas('employeeDetail', fn ($detail) => $detail->where('unit_id', $unitId));
            }, function ($query) {
                $query->whereRaw('1 = 0');
            })
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (User $item) => [
                'id' => $item->id,
                'name' => $item->name,
            ])
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Pilihan tukar shift berhasil diambil',
            'data' => [
                'unit' => $user->employeeDetail?->unit?->nama_unit,
                'my_shifts' => $myShifts,
                'users' => $users,
            ],
        ]);
    }

    public function availableTargetShifts(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user()->loadMissing('employeeDetail');
        $roleResponse = $this->ensureUser($user);
        if ($roleResponse) {
            return $roleResponse;
        }

        $validated = $request->validate([
            'target_user_id' => ['required', 'integer', 'exists:users,id'],
            'shift_id' => ['required', 'integer', 'exists:shift_schedules,id'],
        ]);

        $myShift = ShiftSchedule::query()
            ->where('id', $validated['shift_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        $currentUnitId = $user->employeeDetail?->unit_id;
        if (!$currentUnitId) {
            return response()->json(['success' => true, 'message' => 'Tidak ada shift target', 'data' => []]);
        }

        $targetUserValid = User::query()
            ->where('id', $validated['target_user_id'])
            ->where('role', 'user')
            ->where('id', '!=', $user->id)
            ->whereHas('employeeDetail', fn ($detail) => $detail->where('unit_id', $currentUnitId))
            ->exists();

        if (!$targetUserValid) {
            return response()->json(['success' => true, 'message' => 'User target tidak valid', 'data' => []]);
        }

        $items = ShiftSchedule::query()
            ->where('user_id', $validated['target_user_id'])
            ->whereDate('tanggal', $myShift->tanggal)
            ->where('status', 'aktif')
            ->orderBy('jam_masuk')
            ->get()
            ->map(fn (ShiftSchedule $item) => $this->formatShift($item))
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Shift target berhasil diambil',
            'data' => $items,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user()->loadMissing('employeeDetail');
        $roleResponse = $this->ensureUser($user);
        if ($roleResponse) {
            return $roleResponse;
        }

        $data = $request->validate([
            'shift_id' => ['required', 'integer', 'exists:shift_schedules,id'],
            'target_user_id' => ['required', 'integer', 'exists:users,id'],
            'target_shift_id' => ['required', 'integer', 'exists:shift_schedules,id'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $myShift = ShiftSchedule::query()->findOrFail($data['shift_id']);
        $targetShift = ShiftSchedule::query()->findOrFail($data['target_shift_id']);

        if ((int) $myShift->user_id !== (int) $user->id) {
            return $this->rejectRequest('Shift yang dipilih bukan milik Anda.');
        }

        if ((int) $data['target_user_id'] === (int) $user->id) {
            return $this->rejectRequest('Tidak bisa tukar shift dengan diri sendiri.');
        }

        if ((int) $targetShift->user_id !== (int) $data['target_user_id']) {
            return $this->rejectRequest('Shift target tidak sesuai user yang dipilih.');
        }

        $targetUser = User::query()->with('employeeDetail')->findOrFail($data['target_user_id']);

        if ($targetUser->role !== 'user') {
            return $this->rejectRequest('User target tidak valid untuk tukar shift.');
        }

        if (!$this->usersAreInSameUnit($user, $targetUser)) {
            return $this->rejectRequest('Tukar shift hanya bisa dengan pegawai dalam unit yang sama.');
        }

        if ($myShift->tanggal->toDateString() !== $targetShift->tanggal->toDateString()) {
            return $this->rejectRequest('Tukar shift hanya bisa di tanggal yang sama.');
        }

        $myStart = Carbon::parse($myShift->tanggal->toDateString() . ' ' . $myShift->jam_masuk->format('H:i:s'));
        $targetStart = Carbon::parse($targetShift->tanggal->toDateString() . ' ' . $targetShift->jam_masuk->format('H:i:s'));

        if ($myStart->isPast() || $targetStart->isPast()) {
            return $this->rejectRequest('Shift yang sudah lewat tidak bisa ditukar.');
        }

        $pendingExists = ShiftSwap::query()
            ->where('status', 'pending')
            ->where(function ($query) use ($myShift, $targetShift) {
                $query->whereIn('shift_id', [$myShift->id, $targetShift->id])
                    ->orWhereIn('target_shift_id', [$myShift->id, $targetShift->id]);
            })
            ->exists();

        if ($pendingExists) {
            return $this->rejectRequest('Salah satu shift sudah memiliki request pending.');
        }

        $swap = ShiftSwap::query()->create([
            'requester_id' => $user->id,
            'target_user_id' => $data['target_user_id'],
            'shift_id' => $myShift->id,
            'target_shift_id' => $targetShift->id,
            'status' => 'pending',
            'note' => $data['note'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Request tukar shift berhasil dikirim. Menunggu respon target user dan admin.',
            'data' => $this->formatSwap($swap->load(['requester', 'targetUser', 'shift', 'targetShift']), $user->id),
        ], 201);
    }

    public function accept(Request $request, ShiftSwap $shiftSwap): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ((int) $shiftSwap->target_user_id !== (int) $user->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        if ($shiftSwap->status !== 'pending') {
            return $this->rejectRequest('Request ini sudah diproses.', 409);
        }

        $note = trim(($shiftSwap->note ? $shiftSwap->note . "\n" : '') . 'Target user menerima request pada ' . now()->format('d/m/Y H:i'));
        $shiftSwap->update(['note' => $note]);

        return response()->json([
            'success' => true,
            'message' => 'Request diterima oleh target user dan menunggu keputusan admin.',
            'data' => $this->formatSwap($shiftSwap->fresh(['requester', 'targetUser', 'shift', 'targetShift']), $user->id),
        ]);
    }

    public function reject(Request $request, ShiftSwap $shiftSwap): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ((int) $shiftSwap->target_user_id !== (int) $user->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        if ($shiftSwap->status !== 'pending') {
            return $this->rejectRequest('Request ini sudah diproses.', 409);
        }

        $note = trim(($shiftSwap->note ? $shiftSwap->note . "\n" : '') . 'Target user menolak request pada ' . now()->format('d/m/Y H:i'));

        $shiftSwap->update([
            'status' => 'rejected',
            'note' => $note,
            'approved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Request tukar shift berhasil ditolak.',
            'data' => $this->formatSwap($shiftSwap->fresh(['requester', 'targetUser', 'shift', 'targetShift']), $user->id),
        ]);
    }

    private function ensureUser(User $user): ?JsonResponse
    {
        if ($user->role === 'user') {
            return null;
        }

        return response()->json(['success' => false, 'message' => 'Endpoint ini hanya untuk user.'], 403);
    }

    private function rejectRequest(string $message, int $status = 422): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message], $status);
    }

    private function usersAreInSameUnit(User $firstUser, User $secondUser): bool
    {
        $firstUnitId = $firstUser->employeeDetail?->unit_id;
        $secondUnitId = $secondUser->employeeDetail?->unit_id;

        return $firstUnitId !== null && (int) $firstUnitId === (int) $secondUnitId;
    }

    private function formatSwap(ShiftSwap $swap, int $currentUserId): array
    {
        return [
            'id' => $swap->id,
            'status' => $swap->status,
            'note' => $swap->note,
            'requester' => ['id' => $swap->requester?->id, 'name' => $swap->requester?->name],
            'target_user' => ['id' => $swap->targetUser?->id, 'name' => $swap->targetUser?->name],
            'shift' => $swap->shift ? $this->formatShift($swap->shift) : null,
            'target_shift' => $swap->targetShift ? $this->formatShift($swap->targetShift) : null,
            'approved_by' => $swap->approver?->name,
            'approved_at' => $swap->approved_at?->toIso8601String(),
            'created_at' => $swap->created_at?->toIso8601String(),
            'is_target' => (int) $swap->target_user_id === $currentUserId,
            'can_respond' => (int) $swap->target_user_id === $currentUserId && $swap->status === 'pending',
        ];
    }

    private function formatShift(ShiftSchedule $shift): array
    {
        return [
            'id' => $shift->id,
            'tanggal' => $shift->tanggal?->toDateString(),
            'jam_masuk' => $shift->jam_masuk?->format('H:i'),
            'jam_pulang' => $shift->jam_pulang?->format('H:i'),
            'status' => $shift->status,
            'shift_code' => $shift->shift_code,
            'nama_shift' => $shift->nama_shift,
        ];
    }
}
