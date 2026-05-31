<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\FeatureSetting;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\Presensi;
use App\Models\ShiftSchedule;
use App\Models\User;
use App\Models\WorkSetting;
use App\Services\LeavePolicyService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        if (in_array($request->query('jenis_izin'), ['sakit', 'cuti'], true)) {
            abort_unless(FeatureSetting::enabled($request->query('jenis_izin'), 'admin'), 403, 'Anda tidak memiliki akses ke fitur ini.');
        }

        $requests = LeaveRequest::query()
            ->with(['user.employeeDetail.department', 'approver', 'overtimeRequest.user'])
            ->when($request->filled('jenis_izin'), fn ($query) => $query->where('jenis_izin', $request->jenis_izin))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->user_id))
            ->when($request->filled('unit_id'), function ($query) use ($request) {
                $query->whereHas('user.employeeDetail', fn ($detail) => $detail->where('department_id', $request->unit_id));
            })
            ->when($request->filled('tanggal'), function ($query) use ($request) {
                $tanggal = Carbon::parse($request->tanggal)->toDateString();
                $query->whereDate('tanggal_mulai', '<=', $tanggal)
                    ->whereDate('tanggal_selesai', '>=', $tanggal);
            })
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        $users = User::query()->where('role', 'user')->orderBy('name')->get();
        $units = Department::query()->orderBy('nama_departemen')->get();

        return view('admin.leave_requests.index', compact('requests', 'users', 'units'));
    }

    public function update(Request $request, LeaveRequest $leaveRequest, LeavePolicyService $leavePolicy)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'catatan_admin' => ['nullable', 'string'],
            'replacement_user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where('role', 'user'),
            ],
            'compensation_type' => ['nullable', 'required_with:replacement_user_id', 'in:uang,libur_pengganti'],
        ]);

        if ($validated['status'] === 'approved' && $leaveRequest->jenis_izin === 'cuti') {
            $leavePolicy->validateCutiRequest(
                $leaveRequest->user,
                $leaveRequest->tanggal_mulai,
                $leaveRequest->tanggal_selesai,
                ['approved'],
                $leaveRequest->id,
                false
            );
        }

        if (
            $validated['status'] === 'approved' &&
            $leaveRequest->jenis_izin === 'sakit' &&
            !empty($validated['replacement_user_id']) &&
            (int) $validated['replacement_user_id'] === (int) $leaveRequest->user_id
        ) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'replacement_user_id' => 'Pegawai pengganti tidak boleh sama dengan pegawai yang sakit.',
            ]);
        }

        $leaveRequest->update([
            'status' => $validated['status'],
            'catatan_admin' => $validated['catatan_admin'] ?? null,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        $this->syncSickReplacementOvertime($leaveRequest->fresh('user'), $validated);

        return back()->with('success', 'Status izin berhasil diperbarui.');
    }

    private function syncSickReplacementOvertime(LeaveRequest $leaveRequest, array $validated): void
    {
        if ($leaveRequest->jenis_izin !== 'sakit') {
            return;
        }

        if ($validated['status'] !== 'approved') {
            $leaveRequest->overtimeRequest?->update(['status' => 'cancelled']);
            $this->removeAutomaticSickPresensi($leaveRequest);

            return;
        }

        $this->syncAutomaticSickPresensi($leaveRequest);

        if (empty($validated['replacement_user_id'])) {
            $leaveRequest->overtimeRequest?->update(['status' => 'cancelled']);

            return;
        }

        $firstSchedule = ShiftSchedule::query()
            ->where('user_id', $leaveRequest->user_id)
            ->whereBetween('tanggal', [
                $leaveRequest->tanggal_mulai->toDateString(),
                $leaveRequest->tanggal_selesai->toDateString(),
            ])
            ->orderBy('tanggal')
            ->first();
        $workSetting = WorkSetting::query()->first();

        OvertimeRequest::query()->updateOrCreate(
            ['leave_request_id' => $leaveRequest->id],
            [
                'user_id' => $validated['replacement_user_id'],
                'source_user_id' => $leaveRequest->user_id,
                'source_type' => 'sakit_pengganti',
                'tanggal_mulai' => $leaveRequest->tanggal_mulai,
                'tanggal_selesai' => $leaveRequest->tanggal_selesai,
                'jam_mulai' => $firstSchedule?->jam_masuk?->format('H:i:s') ?? $workSetting?->jam_masuk ?? '08:00:00',
                'jam_selesai' => $firstSchedule?->jam_pulang?->format('H:i:s') ?? $workSetting?->jam_pulang ?? '16:00:00',
                'compensation_type' => $validated['compensation_type'] ?? OvertimeRequest::COMPENSATION_MONEY,
                'status' => 'approved',
                'keterangan' => 'Lembur pengganti sakit untuk ' . ($leaveRequest->user?->name ?? 'pegawai'),
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]
        );
    }

    private function syncAutomaticSickPresensi(LeaveRequest $leaveRequest): void
    {
        $cursor = $leaveRequest->tanggal_mulai->copy()->startOfDay();

        while ($cursor->lte($leaveRequest->tanggal_selesai)) {
            Presensi::query()->updateOrCreate(
                [
                    'user_id' => $leaveRequest->user_id,
                    'tanggal' => $cursor->toDateString(),
                ],
                [
                    'status' => 'sakit',
                    'status_pulang' => null,
                ]
            );

            $cursor->addDay();
        }
    }

    private function removeAutomaticSickPresensi(LeaveRequest $leaveRequest): void
    {
        Presensi::query()
            ->where('user_id', $leaveRequest->user_id)
            ->whereBetween('tanggal', [
                $leaveRequest->tanggal_mulai->toDateString(),
                $leaveRequest->tanggal_selesai->toDateString(),
            ])
            ->where('status', 'sakit')
            ->whereNull('jam_masuk')
            ->whereNull('jam_keluar')
            ->delete();
    }
}
