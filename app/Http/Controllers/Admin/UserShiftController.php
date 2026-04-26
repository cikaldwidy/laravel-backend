<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\User;
use App\Models\UserShift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserShiftController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date', now()->toDateString());
        $tanggal = Carbon::parse($date)->toDateString();

        $shifts = Shift::query()
            ->orderBy('nama_shift')
            ->get();

        $users = User::query()
            ->where('role', 'user')
            ->orderBy('name')
            ->get();

        $assignments = UserShift::query()
            ->whereDate('tanggal', $tanggal)
            ->pluck('shift_id', 'user_id');

        return view('admin.user_shifts.index', [
            'tanggal' => $tanggal,
            'shifts' => $shifts,
            'users' => $users,
            'assignments' => $assignments,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'shift' => ['required', 'array'],
            'shift.*' => ['nullable', 'integer', 'exists:shifts,id'],
        ]);

        $tanggal = Carbon::parse($validated['tanggal'])->toDateString();
        $shiftByUserId = $validated['shift'];

        DB::transaction(function () use ($tanggal, $shiftByUserId) {
            foreach ($shiftByUserId as $userId => $shiftId) {
                $userId = (int) $userId;
                $shiftId = $shiftId !== null && $shiftId !== '' ? (int) $shiftId : null;

                if ($shiftId === null) {
                    UserShift::query()
                        ->where('user_id', $userId)
                        ->whereDate('tanggal', $tanggal)
                        ->delete();
                    continue;
                }

                UserShift::updateOrCreate(
                    ['user_id' => $userId, 'tanggal' => $tanggal],
                    ['shift_id' => $shiftId]
                );
            }
        });

        return redirect()
            ->route('admin.user_shifts.index', ['date' => $tanggal])
            ->with('success', 'Jadwal shift berhasil disimpan.');
    }
}

