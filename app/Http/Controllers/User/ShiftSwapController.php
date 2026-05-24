<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShiftSwapRequest;
use App\Models\Announcement;
use App\Models\ShiftSchedule;
use App\Models\ShiftSwap;
use App\Models\User;
use App\Services\AdminPushService;
use App\Services\AnnouncementPushService;
use App\Support\ShiftTime;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ShiftSwapController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = ShiftSwap::query()
            ->with(['requester', 'targetUser', 'shift', 'targetShift', 'approver'])
            ->where(function ($q) {
                $q->where('requester_id', auth()->id())
                    ->orWhere('target_user_id', auth()->id());
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);

                $query->where(function ($q) use ($search) {
                    $q->where('note', 'like', '%' . $search . '%')
                        ->orWhereHas('requester', fn ($user) => $user->where('name', 'like', '%' . $search . '%'))
                        ->orWhereHas('targetUser', fn ($user) => $user->where('name', 'like', '%' . $search . '%'));
                });
            })
            ->latest();

        if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $status);
        }

        $swaps = $query->paginate(10)->withQueryString();

        return view('user.shift_swaps.index', compact('swaps', 'status'));
    }

    public function create()
    {
        $currentUser = auth()->user()->loadMissing('employeeDetail.unit');
        $unitId = $currentUser->employeeDetail?->unit_id;
        $unitName = $currentUser->employeeDetail?->unit?->nama_unit;

        $myShifts = ShiftSchedule::query()
            ->where('user_id', auth()->id())
            ->whereDate('tanggal', '>=', today()->subDay())
            ->where('status', 'aktif')
            ->whereDoesntHave('requestedSwaps', fn ($query) => $query->where('status', 'pending'))
            ->whereDoesntHave('targetSwaps', fn ($query) => $query->where('status', 'pending'))
            ->orderBy('tanggal')
            ->orderBy('jam_masuk')
            ->get()
            ->filter(fn (ShiftSchedule $item) => $this->shiftHasNotEnded($item))
            ->values();

        $users = User::query()
            ->where('role', 'user')
            ->where('id', '!=', auth()->id())
            ->when($unitId, function ($query) use ($unitId) {
                $query->whereHas('employeeDetail', fn ($detail) => $detail->where('unit_id', $unitId));
            }, function ($query) {
                $query->whereRaw('1 = 0');
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('user.shift_swaps.create', compact('myShifts', 'users', 'unitName'));
    }

    public function availableTargetShifts(Request $request)
    {
        $validated = $request->validate([
            'target_user_id' => ['required', 'integer', 'exists:users,id'],
            'shift_id' => ['nullable', 'integer', 'exists:shift_schedules,id'],
        ]);

        if (!empty($validated['shift_id'])) {
            ShiftSchedule::query()
                ->where('id', $validated['shift_id'])
                ->where('user_id', auth()->id())
                ->firstOrFail();
        }

        $currentUnitId = auth()->user()?->employeeDetail?->unit_id;

        if (!$currentUnitId) {
            return response()->json([]);
        }

        $targetUserValid = User::query()
            ->where('id', $validated['target_user_id'])
            ->where('role', 'user')
            ->where('id', '!=', auth()->id())
            ->whereHas('employeeDetail', fn ($detail) => $detail->where('unit_id', $currentUnitId))
            ->exists();

        if (!$targetUserValid) {
            return response()->json([]);
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
            ->values()
            ->map(function (ShiftSchedule $item) {
                return [
                    'id' => $item->id,
                    'text' => $item->tanggal->format('d/m/Y') . ' | ' . $this->toTime($item->jam_masuk, 'H:i') . ' - ' . $this->toTime($item->jam_pulang, 'H:i'),
                ];
            })
            ->values();

        return response()->json($items);
    }

    public function store(StoreShiftSwapRequest $request, AnnouncementPushService $announcementPush, AdminPushService $adminPush)
    {
        $data = $request->validated();

        $myShift = ShiftSchedule::query()->findOrFail($data['shift_id']);
        $targetShift = ShiftSchedule::query()->findOrFail($data['target_shift_id']);

        if ((int) $myShift->user_id !== (int) auth()->id()) {
            return back()->withErrors(['shift_id' => 'Shift yang dipilih bukan milik Anda.'])->withInput();
        }

        if ((int) $data['target_user_id'] === (int) auth()->id()) {
            return back()->withErrors(['target_user_id' => 'Tidak bisa tukar shift dengan diri sendiri.'])->withInput();
        }

        if ((int) $targetShift->user_id !== (int) $data['target_user_id']) {
            return back()->withErrors(['target_shift_id' => 'Shift target tidak sesuai user yang dipilih.'])->withInput();
        }

        $requester = User::query()->with('employeeDetail')->findOrFail(auth()->id());
        $targetUser = User::query()->with('employeeDetail')->findOrFail($data['target_user_id']);

        if ($targetUser->role !== 'user') {
            return back()->withErrors(['target_user_id' => 'User target tidak valid untuk tukar shift.'])->withInput();
        }

        if (!$this->usersAreInSameUnit($requester, $targetUser)) {
            return back()->withErrors(['target_user_id' => 'Tukar shift hanya bisa dengan pegawai dalam unit yang sama.'])->withInput();
        }

        if ($myShift->status !== 'aktif' || $targetShift->status !== 'aktif') {
            return back()->withErrors(['shift_id' => 'Hanya jadwal aktif yang bisa ditukar.'])->withInput();
        }

        if (!$this->shiftHasNotEnded($myShift) || !$this->shiftHasNotEnded($targetShift)) {
            return back()->withErrors(['shift_id' => 'Shift yang sudah selesai tidak bisa ditukar.'])->withInput();
        }

        $pendingExists = ShiftSwap::query()
            ->where('status', 'pending')
            ->where(function ($q) use ($myShift, $targetShift) {
                $q->whereIn('shift_id', [$myShift->id, $targetShift->id])
                    ->orWhereIn('target_shift_id', [$myShift->id, $targetShift->id]);
            })
            ->exists();

        if ($pendingExists) {
            return back()->withErrors(['shift_id' => 'Salah satu shift sudah memiliki request pending.'])->withInput();
        }

        $swap = ShiftSwap::create([
            'requester_id' => auth()->id(),
            'target_user_id' => $data['target_user_id'],
            'shift_id' => $myShift->id,
            'target_shift_id' => $targetShift->id,
            'status' => 'pending',
            'note' => $data['note'] ?? null,
        ]);

        $swap->load(['requester', 'targetUser', 'shift', 'targetShift']);

        $this->publishSwapAnnouncement($swap, $announcementPush);
        $this->notifyAdminsAboutSwap($swap, $adminPush);

        return redirect()->route('shift-swaps.index')->with('success', 'Request tukar shift berhasil dikirim. Menunggu keputusan admin.');
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

    private function shiftHasNotEnded(ShiftSchedule $shift): bool
    {
        $end = ShiftTime::endAt(
            $shift->tanggal,
            $this->toTime($shift->jam_masuk, 'H:i:s'),
            $this->toTime($shift->jam_pulang, 'H:i:s')
        );

        return !$end->isPast();
    }

    private function usersAreInSameUnit(User $firstUser, User $secondUser): bool
    {
        $firstUnitId = $firstUser->employeeDetail?->unit_id;
        $secondUnitId = $secondUser->employeeDetail?->unit_id;

        return $firstUnitId !== null && (int) $firstUnitId === (int) $secondUnitId;
    }

    private function publishSwapAnnouncement(ShiftSwap $swap, AnnouncementPushService $announcementPush): void
    {
        $requesterShift = $swap->shift
            ? $swap->shift->tanggal->format('d/m/Y') . ' ' . $this->toTime($swap->shift->jam_masuk, 'H:i') . ' - ' . $this->toTime($swap->shift->jam_pulang, 'H:i')
            : '-';
        $targetShift = $swap->targetShift
            ? $swap->targetShift->tanggal->format('d/m/Y') . ' ' . $this->toTime($swap->targetShift->jam_masuk, 'H:i') . ' - ' . $this->toTime($swap->targetShift->jam_pulang, 'H:i')
            : '-';

        $announcement = Announcement::create([
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
            'action_url' => route('shift-swaps.index', [], false),
        ]);

        $announcement->users()->sync([
            $swap->requester_id,
            $swap->target_user_id,
        ]);

        $announcementPush->send($announcement, route('shift-swaps.index', [], false));
    }

    private function notifyAdminsAboutSwap(ShiftSwap $swap, AdminPushService $adminPush): void
    {
        $adminPush->send([
            'title' => 'Tukar Shift Baru',
            'body' => ($swap->requester?->name ?? 'Pegawai') . ' mengajukan tukar shift dengan ' . ($swap->targetUser?->name ?? 'pegawai lain') . '.',
            'url' => route('admin.shift_management.swaps', ['status' => 'pending'], false),
            'tag' => 'admin-shift-swap-' . $swap->id,
            'renotify' => true,
        ]);
    }
}
