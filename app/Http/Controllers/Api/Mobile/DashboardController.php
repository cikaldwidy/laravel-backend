<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\LeaveRequest;
use App\Models\Presensi;
use App\Models\ShiftSchedule;
use App\Models\User;
use App\Models\WorkSetting;
use App\Support\ShiftTime;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user()->loadMissing(['employeeDetail.unit', 'faceEmbedding']);

        if ($user->role !== 'user') {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint ini hanya untuk user.',
            ], 403);
        }

        $today = today();
        $now = now();

        $presensiHariIni = Presensi::query()
            ->where('user_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        $rekapQuery = Presensi::query()
            ->where('user_id', $user->id)
            ->whereDate('tanggal', '>=', $today->copy()->subDays(29));

        $workSetting = WorkSetting::query()->first();
        $scheduledShift = ShiftSchedule::query()
            ->where('user_id', $user->id)
            ->whereDate('tanggal', $now->toDateString())
            ->first();
        $activeShiftContext = $this->resolveActiveShift($user, $now, $workSetting);
        $approvedLeaveToday = LeaveRequest::query()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today)
            ->latest('approved_at')
            ->first();

        $announcements = Announcement::query()
            ->with('unit')
            ->where('is_published', true)
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_berakhir', '>=', $today)
            ->where(function ($query) use ($user) {
                $query->where('target_type', 'all');

                if ($user->employeeDetail?->unit_id) {
                    $query->orWhere(function ($unitQuery) use ($user) {
                        $unitQuery->where('target_type', 'unit')
                            ->where('unit_id', $user->employeeDetail?->unit_id);
                    });
                }
            })
            ->latest('tanggal_mulai')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Dashboard berhasil diambil',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'unit' => $user->employeeDetail?->unit?->nama_unit,
                    'has_face_enrollment' => $user->faceEmbedding !== null,
                ],
                'presensi_hari_ini' => $this->formatPresensi($presensiHariIni),
                'status_presensi' => [
                    'can_masuk' => !$presensiHariIni || !$presensiHariIni->jam_masuk,
                    'can_pulang' => (bool) ($presensiHariIni?->jam_masuk && !$presensiHariIni?->jam_keluar),
                    'has_approved_leave' => $approvedLeaveToday !== null,
                    'active_shift_available' => $activeShiftContext !== null,
                ],
                'rekap_30_hari' => [
                    'hadir' => (clone $rekapQuery)->where('status', 'hadir')->count(),
                    'terlambat' => (clone $rekapQuery)->whereIn('status', ['telat', 'terlambat'])->count(),
                    'pulang_cepat' => (clone $rekapQuery)->where('status_pulang', 'pulang_cepat')->count(),
                    'izin' => (clone $rekapQuery)->where('status', 'izin')->count(),
                    'total' => (clone $rekapQuery)->count(),
                ],
                'shift' => [
                    'scheduled' => $this->formatShift($scheduledShift),
                    'active' => $this->formatShift($activeShiftContext['shift'] ?? null),
                ],
                'pengumuman' => $announcements->map(fn (Announcement $announcement) => [
                    'id' => $announcement->id,
                    'judul' => $announcement->judul,
                    'isi' => $announcement->isi,
                    'unit' => $announcement->unit?->nama_unit,
                    'tanggal_mulai' => $announcement->tanggal_mulai?->toDateString(),
                    'tanggal_berakhir' => $announcement->tanggal_berakhir?->toDateString(),
                ])->values(),
            ],
        ]);
    }

    private function resolveActiveShift(User $user, Carbon $now, ?WorkSetting $setting = null): ?array
    {
        $checkinEarlyMinutes = (int) ($setting?->checkin_early_minutes ?? WorkSetting::DEFAULT_CHECKIN_EARLY_MINUTES);
        $checkoutLateMinutes = (int) ($setting?->checkout_late_minutes ?? WorkSetting::DEFAULT_CHECKOUT_LATE_MINUTES);

        $shiftCandidates = ShiftSchedule::query()
            ->where('user_id', $user->id)
            ->where('status', 'aktif')
            ->whereIn('tanggal', [
                $now->toDateString(),
                $now->copy()->subDay()->toDateString(),
            ])
            ->orderByDesc('tanggal')
            ->get();

        foreach ($shiftCandidates as $candidate) {
            $shiftDate = Carbon::parse($candidate->tanggal)->startOfDay();
            $window = ShiftTime::window(
                $shiftDate,
                $candidate->jam_masuk->format('H:i:s'),
                $candidate->jam_pulang->format('H:i:s'),
                $checkinEarlyMinutes,
                $checkoutLateMinutes
            );

            if ($now->between($window['allowed_start'], $window['allowed_end'], true)) {
                return [
                    'shift' => $candidate,
                    'shift_date' => $shiftDate,
                    'start' => $window['start'],
                    'end' => $window['end'],
                ];
            }
        }

        return null;
    }

    private function formatPresensi(?Presensi $presensi): ?array
    {
        if (!$presensi) {
            return null;
        }

        return [
            'id' => $presensi->id,
            'tanggal' => $presensi->tanggal?->toDateString(),
            'jam_masuk' => $presensi->jam_masuk?->format('H:i'),
            'jam_keluar' => $presensi->jam_keluar?->format('H:i'),
            'status' => $presensi->status,
            'status_pulang' => $presensi->status_pulang,
        ];
    }

    private function formatShift(?ShiftSchedule $shift): ?array
    {
        if (!$shift) {
            return null;
        }

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
