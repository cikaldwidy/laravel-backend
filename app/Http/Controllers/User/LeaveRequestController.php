<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\FeatureSetting;
use App\Models\LeaveRequest;
use App\Services\AdminPushService;
use App\Services\LeavePolicyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LeaveRequestController extends Controller
{
    private const TYPES = ['sakit', 'cuti'];

    public function index(Request $request)
    {
        $user = Auth::user();

        $requests = LeaveRequest::query()
            ->where('user_id', $user->id)
            ->whereIn('jenis_izin', self::TYPES)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);

                $query->where(function ($q) use ($search) {
                    $q->where('jenis_izin', 'like', '%' . $search . '%')
                        ->orWhere('keterangan', 'like', '%' . $search . '%')
                        ->orWhere('catatan_admin', 'like', '%' . $search . '%');
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('jenis_izin'), fn ($query) => $query->where('jenis_izin', $request->jenis_izin))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('tanggal_selesai', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('tanggal_mulai', '<=', $request->date_to))
            ->latest('tanggal_mulai')
            ->latest('created_at')
            ->get();

        return view('user.leave_requests.index', compact('requests'));
    }

    public function create(Request $request, LeavePolicyService $leavePolicy)
    {
        $selectedJenisIzin = old('jenis_izin', $request->query('jenis_izin', 'sakit'));
        $backUrl = $request->query('back') === 'dashboard'
            ? route('dashboard')
            : route('leave_requests.index');
        $cutiQuota = null;

        abort_unless(in_array($selectedJenisIzin, self::TYPES, true), 404);

        abort_unless(FeatureSetting::enabled($selectedJenisIzin, 'user'), 403, 'Anda tidak memiliki akses ke fitur ini.');

        if ($selectedJenisIzin === 'cuti') {
            $cutiQuota = $leavePolicy->annualQuotaSummary(Auth::user());
        }

        return view('user.leave_requests.create', compact('selectedJenisIzin', 'backUrl', 'cutiQuota'));
    }

    public function store(Request $request, AdminPushService $adminPush, LeavePolicyService $leavePolicy)
    {
        $validated = $request->validate([
            'jenis_izin' => ['required', 'string', 'max:50', Rule::in(self::TYPES)],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'keterangan' => ['nullable', 'string'],
            'lampiran' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        abort_unless(FeatureSetting::enabled($validated['jenis_izin'], 'user'), 403, 'Anda tidak memiliki akses ke fitur ini.');

        if ($validated['jenis_izin'] === 'cuti') {
            $leavePolicy->validateCutiRequest(
                Auth::user(),
                $validated['tanggal_mulai'],
                $validated['tanggal_selesai']
            );
        }

        $lampiranPath = $request->hasFile('lampiran')
            ? $request->file('lampiran')->store('leave-attachments', 'public')
            : null;

        $leaveRequest = LeaveRequest::create([
            'user_id' => Auth::id(),
            'jenis_izin' => $validated['jenis_izin'],
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
            'keterangan' => $validated['keterangan'] ?? '',
            'lampiran' => $lampiranPath,
            'status' => 'pending',
        ]);

        $adminPush->send([
            'title' => 'Pengajuan Izin Baru',
            'body' => (Auth::user()?->name ?? 'Pegawai') . ' mengajukan ' . ucfirst($leaveRequest->jenis_izin) . '.',
            'url' => route('admin.leave_requests.index', ['status' => 'pending'], false),
            'tag' => 'admin-leave-request-' . $leaveRequest->id,
            'renotify' => true,
        ]);

        return redirect()->route('leave_requests.index')->with('success', 'Pengajuan izin berhasil dikirim.');
    }

    public function destroy(LeaveRequest $leaveRequest)
    {
        abort_unless($leaveRequest->user_id === Auth::id() && $leaveRequest->status === 'pending', 403);

        if ($leaveRequest->lampiran) {
            Storage::disk('public')->delete($leaveRequest->lampiran);
        }

        $leaveRequest->delete();

        return back()->with('success', 'Pengajuan izin berhasil dihapus.');
    }
}
