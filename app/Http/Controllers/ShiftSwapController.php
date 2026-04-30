<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShiftSwapRequest;
use App\Models\ShiftSchedule;
use App\Models\ShiftSwap;
use App\Models\User;
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
            ->whereDate('tanggal', '>=', today())
            ->where('status', 'aktif')
            ->orderBy('tanggal')
            ->orderBy('jam_masuk')
            ->get();

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
            'shift_id' => ['required', 'integer', 'exists:shift_schedules,id'],
        ]);

        $myShift = ShiftSchedule::query()
            ->where('id', $validated['shift_id'])
            ->where('user_id', auth()->id())
            ->firstOrFail();

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
            ->whereDate('tanggal', $myShift->tanggal)
            ->where('status', 'aktif')
            ->orderBy('jam_masuk')
            ->get()
            ->map(function (ShiftSchedule $item) {
                return [
                    'id' => $item->id,
                    'text' => $item->tanggal->format('d/m/Y') . ' | ' . $this->toTime($item->jam_masuk, 'H:i') . ' - ' . $this->toTime($item->jam_pulang, 'H:i'),
                ];
            });

        return response()->json($items);
    }

    public function store(StoreShiftSwapRequest $request)
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

        if ($myShift->tanggal->toDateString() !== $targetShift->tanggal->toDateString()) {
            return back()->withErrors(['target_shift_id' => 'Tukar shift hanya bisa di tanggal yang sama.'])->withInput();
        }

        $myStart = Carbon::parse($myShift->tanggal->toDateString() . ' ' . $this->toTime($myShift->jam_masuk, 'H:i:s'));
        $targetStart = Carbon::parse($targetShift->tanggal->toDateString() . ' ' . $this->toTime($targetShift->jam_masuk, 'H:i:s'));

        if ($myStart->isPast() || $targetStart->isPast()) {
            return back()->withErrors(['shift_id' => 'Shift yang sudah lewat tidak bisa ditukar.'])->withInput();
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

        ShiftSwap::create([
            'requester_id' => auth()->id(),
            'target_user_id' => $data['target_user_id'],
            'shift_id' => $myShift->id,
            'target_shift_id' => $targetShift->id,
            'status' => 'pending',
            'note' => $data['note'] ?? null,
        ]);

        return redirect()->route('shift-swaps.index')->with('success', 'Request tukar shift berhasil dikirim. Menunggu respon target user dan admin.');
    }

    public function targetAccept(ShiftSwap $shiftSwap)
    {
        if ((int) $shiftSwap->target_user_id !== (int) auth()->id()) {
            abort(403);
        }

        if ($shiftSwap->status !== 'pending') {
            return back()->with('error', 'Request ini sudah diproses.');
        }

        $note = trim(($shiftSwap->note ? $shiftSwap->note . "\n" : '') . 'Target user menerima request pada ' . now()->format('d/m/Y H:i'));
        $shiftSwap->update(['note' => $note]);

        return back()->with('success', 'Request diterima oleh target user dan menunggu keputusan admin.');
    }

    public function targetReject(ShiftSwap $shiftSwap)
    {
        if ((int) $shiftSwap->target_user_id !== (int) auth()->id()) {
            abort(403);
        }

        if ($shiftSwap->status !== 'pending') {
            return back()->with('error', 'Request ini sudah diproses.');
        }

        $note = trim(($shiftSwap->note ? $shiftSwap->note . "\n" : '') . 'Target user menolak request pada ' . now()->format('d/m/Y H:i'));

        $shiftSwap->update([
            'status' => 'rejected',
            'note' => $note,
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Request tukar shift berhasil ditolak.');
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

    private function usersAreInSameUnit(User $firstUser, User $secondUser): bool
    {
        $firstUnitId = $firstUser->employeeDetail?->unit_id;
        $secondUnitId = $secondUser->employeeDetail?->unit_id;

        return $firstUnitId !== null && (int) $firstUnitId === (int) $secondUnitId;
    }
}
