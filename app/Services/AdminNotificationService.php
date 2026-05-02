<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\Presensi;
use App\Models\ShiftSchedule;
use App\Models\ShiftSwap;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AdminNotificationService
{
    public function items(int $limit = 8): Collection
    {
        $today = today()->toDateString();

        $leaveItems = LeaveRequest::query()
            ->with('user')
            ->where('status', 'pending')
            ->latest()
            ->limit(4)
            ->get()
            ->map(fn (LeaveRequest $item) => [
                'title' => 'Pengajuan izin baru',
                'message' => ($item->user?->name ?? 'Pegawai') . ' mengajukan ' . ucfirst($item->jenis_izin) . '.',
                'icon' => 'fa-solid fa-file-circle-check',
                'tone' => 'bg-amber-50 text-amber-700',
                'url' => route('admin.leave_requests.index', ['status' => 'pending']),
                'time' => $item->created_at,
            ]);

        $swapItems = ShiftSwap::query()
            ->with(['requester', 'targetUser'])
            ->where('status', 'pending')
            ->latest()
            ->limit(4)
            ->get()
            ->map(fn (ShiftSwap $item) => [
                'title' => 'Tukar shift pending',
                'message' => ($item->requester?->name ?? 'Pegawai') . ' ke ' . ($item->targetUser?->name ?? 'pegawai lain') . '.',
                'icon' => 'fa-solid fa-right-left',
                'tone' => 'bg-sky-50 text-sky-700',
                'url' => route('admin.shift_management.swaps', ['status' => 'pending']),
                'time' => $item->created_at,
            ]);

        $lateCount = Presensi::query()
            ->whereDate('tanggal', $today)
            ->whereIn('status', ['telat', 'terlambat'])
            ->count();

        $scheduledUserIds = ShiftSchedule::query()
            ->whereDate('tanggal', $today)
            ->where('status', 'aktif')
            ->pluck('user_id')
            ->unique();

        $presentUserIds = Presensi::query()
            ->whereDate('tanggal', $today)
            ->pluck('user_id')
            ->unique();

        $leaveUserIds = LeaveRequest::query()
            ->where('status', 'approved')
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today)
            ->pluck('user_id')
            ->unique();

        $alphaCount = $scheduledUserIds
            ->diff($presentUserIds)
            ->diff($leaveUserIds)
            ->count();

        $dailyItems = collect([
            $lateCount > 0 ? [
                'title' => 'Pegawai telat hari ini',
                'message' => $lateCount . ' pegawai tercatat telat pada ' . Carbon::parse($today)->format('d/m/Y') . '.',
                'icon' => 'fa-solid fa-clock',
                'tone' => 'bg-orange-50 text-orange-700',
                'url' => route('admin.histories.index', ['tanggal' => $today]),
                'time' => now(),
            ] : null,
            $alphaCount > 0 ? [
                'title' => 'Belum presensi',
                'message' => $alphaCount . ' pegawai terjadwal belum presensi hari ini.',
                'icon' => 'fa-solid fa-user-xmark',
                'tone' => 'bg-red-50 text-red-700',
                'url' => route('admin.shift_management.schedules', ['tanggal' => $today]),
                'time' => now(),
            ] : null,
        ])->filter();

        return $dailyItems
            ->merge($leaveItems)
            ->merge($swapItems)
            ->sortByDesc('time')
            ->take($limit)
            ->values();
    }

    public function count(): int
    {
        $today = today()->toDateString();

        $pendingLeave = LeaveRequest::query()->where('status', 'pending')->count();
        $pendingSwap = ShiftSwap::query()->where('status', 'pending')->count();
        $lateCount = Presensi::query()
            ->whereDate('tanggal', $today)
            ->whereIn('status', ['telat', 'terlambat'])
            ->count();

        $scheduledUserIds = ShiftSchedule::query()
            ->whereDate('tanggal', $today)
            ->where('status', 'aktif')
            ->pluck('user_id')
            ->unique();

        $presentUserIds = Presensi::query()
            ->whereDate('tanggal', $today)
            ->pluck('user_id')
            ->unique();

        $leaveUserIds = LeaveRequest::query()
            ->where('status', 'approved')
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today)
            ->pluck('user_id')
            ->unique();

        return $pendingLeave + $pendingSwap + $lateCount + $scheduledUserIds
            ->diff($presentUserIds)
            ->diff($leaveUserIds)
            ->count();
    }
}
