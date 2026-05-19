<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\ShiftSchedule;
use App\Models\ShiftSwap;
use App\Models\User;
use App\Support\ShiftTime;
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
            ->whereDate('tanggal', '>=', today()->subDay())
            ->where('status', 'aktif')
            ->whereDoesntHave('requestedSwaps', fn ($query) => $query->where('status', 'pending'))
            ->whereDoesntHave('targetSwaps', fn ($query) => $query->where('status', 'pending'))
            ->orderBy('tanggal')
            ->orderBy('jam_masuk')
            ->get()
            ->filter(fn (ShiftSchedule $item) => $this->shiftHasNotEnded($item))
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
            ->whereDate('tanggal', '>=', today()->subDay())
            ->where('status', 'aktif')
            ->whereDoesntHave('requestedSwaps', fn ($query) => $query->where('status', 'pending'))
            ->whereDoesntHave('targetSwaps', fn ($query) => $query->where('status', 'pending'))
            ->orderBy('tanggal')
            ->orderBy('jam_masuk')
            ->get()
            ->filter(fn (ShiftSchedule $item) => $this->shiftHasNotEnded($item))
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

        if (!$this->shiftHasNotEnded($myShift) || !$this->shiftHasNotEnded($targetShift)) {
            return $this->rejectRequest('Shift yang sudah selesai tidak bisa ditukar.');
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

        $this->publishSwapAnnouncement($swap->load(['requester', 'targetUser', 'shift', 'targetShift']));

        return response()->json([
            'success' => true,
            'message' => 'Request tukar shift berhasil dikirim. Menunggu keputusan admin.',
            'data' => $this->formatSwap($swap->load(['requester', 'targetUser', 'shift', 'targetShift']), $user->id),
        ], 201);
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
            'can_respond' => false,
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

    private function shiftHasNotEnded(ShiftSchedule $shift): bool
    {
        $end = ShiftTime::endAt(
            $shift->tanggal,
            $this->toTime($shift->jam_masuk, 'H:i:s'),
            $this->toTime($shift->jam_pulang, 'H:i:s')
        );

        return !$end->isPast();
    }

    private function toTime($value, string $format): string
    {
        if ($value instanceof Carbon) {
            return $value->format($format);
        }

        if (is_string($value)) {
            return Carbon::parse($value)->format($format);
        }

        return Carbon::parse((string) $value)->format($format);
    }

    private function publishSwapAnnouncement(ShiftSwap $swap): void
    {
        $requesterShift = $swap->shift
            ? $swap->shift->tanggal->format('d/m/Y') . ' ' . $this->toTime($swap->shift->jam_masuk, 'H:i') . ' - ' . $this->toTime($swap->shift->jam_pulang, 'H:i')
            : '-';
        $targetShift = $swap->targetShift
            ? $swap->targetShift->tanggal->format('d/m/Y') . ' ' . $this->toTime($swap->targetShift->jam_masuk, 'H:i') . ' - ' . $this->toTime($swap->targetShift->jam_pulang, 'H:i')
            : '-';

        $announcement = Announcement::query()->create([
            'judul' => 'Pemberitahuan Tukar Shift',
            'isi' => trim(
                ($swap->requester?->name ?? 'Pegawai') . ' mengajukan tukar shift dengan ' . ($swap->targetUser?->name ?? 'pegawai target') . ".\n\n"
                . 'Shift yang diajukan: ' . $requesterShift . "\n"
                . 'Shift target: ' . $targetShift . "\n\n"
                . 'Request ini menunggu keputusan admin.'
            ),
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
}
