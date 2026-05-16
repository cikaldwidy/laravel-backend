<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\ShiftSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->role !== 'user') {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint ini hanya untuk user.',
            ], 403);
        }

        $month = $request->query('month', now()->format('Y-m'));

        try {
            $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Throwable) {
            $start = now()->startOfMonth();
            $month = $start->format('Y-m');
        }

        $end = (clone $start)->endOfMonth();

        $schedules = ShiftSchedule::query()
            ->where('user_id', $user->id)
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->orderBy('tanggal')
            ->orderBy('jam_masuk')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Jadwal shift berhasil diambil',
            'data' => [
                'month' => $month,
                'items' => $schedules->map(fn (ShiftSchedule $item) => $this->formatShift($item))->values(),
            ],
        ]);
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
