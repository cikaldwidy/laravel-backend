<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\FeatureSetting;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $requests = LeaveRequest::query()
            ->where('user_id', $user->id)
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

    public function create(Request $request)
    {
        $selectedJenisIzin = old('jenis_izin', $request->query('jenis_izin', 'izin'));
        $backUrl = $request->query('back') === 'dashboard'
            ? route('dashboard')
            : route('leave_requests.index');

        if (in_array($selectedJenisIzin, ['sakit', 'cuti'], true)) {
            abort_unless(FeatureSetting::enabled($selectedJenisIzin, 'user'), 403, 'Anda tidak memiliki akses ke fitur ini.');
        }

        return view('user.leave_requests.create', compact('selectedJenisIzin', 'backUrl'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis_izin' => ['required', 'string', 'max:50'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'keterangan' => ['required', 'string'],
            'lampiran' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        if (in_array($validated['jenis_izin'], ['sakit', 'cuti'], true)) {
            abort_unless(FeatureSetting::enabled($validated['jenis_izin'], 'user'), 403, 'Anda tidak memiliki akses ke fitur ini.');
        }

        $lampiranPath = $request->hasFile('lampiran')
            ? $request->file('lampiran')->store('leave-attachments', 'public')
            : null;

        LeaveRequest::create([
            'user_id' => Auth::id(),
            'jenis_izin' => $validated['jenis_izin'],
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
            'keterangan' => $validated['keterangan'],
            'lampiran' => $lampiranPath,
            'status' => 'pending',
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
