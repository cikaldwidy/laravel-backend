<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\ShiftSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class LeavePolicyService
{
    private const ANNUAL_QUOTAS = [
        'kontrak' => 6,
        'capeg' => 12,
        'tetap' => 12,
        'karyawan_tetap' => 12,
        'training' => 0,
    ];

    private const STATUS_LABELS = [
        'kontrak' => 'Kontrak',
        'capeg' => 'Capeg',
        'tetap' => 'Karyawan Tetap',
        'karyawan_tetap' => 'Karyawan Tetap',
        'training' => 'Training',
    ];

    public function annualQuotaSummary(User $user, ?int $year = null): array
    {
        $year ??= (int) now()->year;
        $status = $this->normalizeEmploymentStatus($user->employeeDetail?->status_kerja);
        $quota = $this->quotaForStatus($status);
        $used = $this->usedLeaveDays($user, $year, ['approved', 'pending']);

        return [
            'year' => $year,
            'status' => $status,
            'status_label' => $this->statusLabel($status),
            'quota_days' => $quota,
            'used_days' => $used,
            'remaining_days' => max(0, $quota - $used),
        ];
    }

    public function validateCutiRequest(
        User $user,
        string|Carbon $startDate,
        string|Carbon $endDate,
        array $countedStatuses = ['approved', 'pending'],
        ?int $excludeLeaveRequestId = null,
        bool $enforceSubmissionRules = true
    ): array {
        $start = $this->asDate($startDate);
        $end = $this->asDate($endDate);

        if ($end->lt($start)) {
            throw ValidationException::withMessages([
                'tanggal_selesai' => 'Tanggal selesai cuti tidak boleh sebelum tanggal mulai.',
            ]);
        }

        $status = $this->normalizeEmploymentStatus($user->employeeDetail?->status_kerja);
        $quota = $this->quotaForStatus($status);

        if ($status === null) {
            throw ValidationException::withMessages([
                'jenis_izin' => 'Status kerja belum lengkap. Lengkapi biodata terlebih dahulu sebelum mengajukan cuti.',
            ]);
        }

        if ($quota <= 0) {
            throw ValidationException::withMessages([
                'jenis_izin' => 'Status ' . $this->statusLabel($status) . ' belum memiliki hak cuti.',
            ]);
        }

        if ($enforceSubmissionRules) {
            $minimumStartDate = today()->addMonthNoOverflow()->startOfDay();
            if ($start->lt($minimumStartDate)) {
                throw ValidationException::withMessages([
                    'tanggal_mulai' => 'Pengajuan cuti harus dibuat minimal 1 bulan sebelum tanggal mulai cuti.',
                ]);
            }

            if ($this->hasPublishedSchedule($user, $start, $end)) {
                throw ValidationException::withMessages([
                    'tanggal_mulai' => 'Jadwal kerja untuk periode cuti ini sudah dibuat. Cuti harus diajukan sebelum jadwal keluar.',
                ]);
            }
        }

        $yearlyRequests = $this->requestedDaysByYear($start, $end);
        $summaries = [];

        foreach ($yearlyRequests as $year => $requestedDays) {
            $usedDays = $this->usedLeaveDays($user, (int) $year, $countedStatuses, $excludeLeaveRequestId);
            $remainingDays = max(0, $quota - $usedDays);

            if ($requestedDays > $remainingDays) {
                throw ValidationException::withMessages([
                    'tanggal_selesai' => "Sisa kuota cuti tahun {$year} adalah {$remainingDays} hari, sedangkan pengajuan ini membutuhkan {$requestedDays} hari.",
                ]);
            }

            $summaries[] = [
                'year' => (int) $year,
                'requested_days' => $requestedDays,
                'used_days' => $usedDays,
                'remaining_days' => $remainingDays,
                'quota_days' => $quota,
            ];
        }

        return [
            'status' => $status,
            'status_label' => $this->statusLabel($status),
            'quota_days' => $quota,
            'requested_days' => $this->countInclusiveDays($start, $end),
            'years' => $summaries,
        ];
    }

    public function requestedDays(string|Carbon $startDate, string|Carbon $endDate): int
    {
        return $this->countInclusiveDays($this->asDate($startDate), $this->asDate($endDate));
    }

    public function quotaForStatus(?string $status): int
    {
        return self::ANNUAL_QUOTAS[$status] ?? 0;
    }

    public function statusLabel(?string $status): string
    {
        return self::STATUS_LABELS[$status] ?? 'Belum Diatur';
    }

    private function usedLeaveDays(
        User $user,
        int $year,
        array $statuses,
        ?int $excludeLeaveRequestId = null
    ): int {
        $yearStart = Carbon::create($year, 1, 1)->startOfDay();
        $yearEnd = Carbon::create($year, 12, 31)->startOfDay();

        return LeaveRequest::query()
            ->where('user_id', $user->id)
            ->where('jenis_izin', 'cuti')
            ->whereIn('status', $statuses)
            ->when($excludeLeaveRequestId, fn ($query) => $query->whereKeyNot($excludeLeaveRequestId))
            ->whereDate('tanggal_mulai', '<=', $yearEnd->toDateString())
            ->whereDate('tanggal_selesai', '>=', $yearStart->toDateString())
            ->get()
            ->sum(function (LeaveRequest $leaveRequest) use ($yearStart, $yearEnd) {
                $start = $this->asDate($leaveRequest->tanggal_mulai)->max($yearStart);
                $end = $this->asDate($leaveRequest->tanggal_selesai)->min($yearEnd);

                return $this->countInclusiveDays($start, $end);
            });
    }

    private function requestedDaysByYear(Carbon $start, Carbon $end): array
    {
        $daysByYear = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $year = (int) $cursor->year;
            $yearEnd = $cursor->copy()->endOfYear()->startOfDay();
            $segmentEnd = $end->lt($yearEnd) ? $end->copy() : $yearEnd;

            $daysByYear[$year] = ($daysByYear[$year] ?? 0) + $this->countInclusiveDays($cursor, $segmentEnd);
            $cursor = $segmentEnd->copy()->addDay();
        }

        return $daysByYear;
    }

    private function hasPublishedSchedule(User $user, Carbon $start, Carbon $end): bool
    {
        return ShiftSchedule::query()
            ->where('user_id', $user->id)
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->exists();
    }

    private function normalizeEmploymentStatus(?string $status): ?string
    {
        $status = strtolower(trim((string) $status));

        return match ($status) {
            'kontrak' => 'kontrak',
            'capeg', 'calon pegawai', 'calon_pegawai' => 'capeg',
            'tetap', 'karyawan tetap', 'karyawan_tetap' => 'tetap',
            'training', 'trainee', 'magang' => 'training',
            default => $status !== '' ? $status : null,
        };
    }

    private function asDate(string|Carbon $value): Carbon
    {
        return $value instanceof Carbon
            ? $value->copy()->startOfDay()
            : Carbon::parse($value)->startOfDay();
    }

    private function countInclusiveDays(Carbon $start, Carbon $end): int
    {
        if ($end->lt($start)) {
            return 0;
        }

        return (int) $start->diffInDays($end) + 1;
    }
}
