<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ShiftSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->query('month', now()->format('Y-m'));

        try {
            $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Throwable $e) {
            $start = now()->startOfMonth();
            $month = $start->format('Y-m');
        }

        $end = (clone $start)->endOfMonth();

        $schedules = ShiftSchedule::query()
            ->where('user_id', auth()->id())
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->orderBy('tanggal')
            ->orderBy('jam_masuk')
            ->get();

        $schedulesByDate = $schedules->groupBy(function (ShiftSchedule $schedule) {
            return $schedule->tanggal->toDateString();
        });

        $daysInMonth = $start->daysInMonth;
        $calendar = collect(range(1, $daysInMonth))->map(function ($day) use ($start, $schedulesByDate) {
            $date = (clone $start)->day($day)->toDateString();
            return [
                'date' => $date,
                'label' => Carbon::parse($date)->translatedFormat('d M'),
                'is_today' => $date === now()->toDateString(),
                'items' => $schedulesByDate->get($date, collect()),
            ];
        });

        return view('user.shifts.index', [
            'month' => $month,
            'calendar' => $calendar,
            'schedules' => $schedules,
        ]);
    }
}
