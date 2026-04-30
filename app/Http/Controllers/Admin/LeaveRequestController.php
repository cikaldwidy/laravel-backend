<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $requests = LeaveRequest::query()
            ->with(['user.employeeDetail.unit', 'approver'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->user_id))
            ->when($request->filled('unit_id'), function ($query) use ($request) {
                $query->whereHas('user.employeeDetail', fn ($detail) => $detail->where('unit_id', $request->unit_id));
            })
            ->when($request->filled('tanggal'), function ($query) use ($request) {
                $tanggal = Carbon::parse($request->tanggal)->toDateString();
                $query->whereDate('tanggal_mulai', '<=', $tanggal)
                    ->whereDate('tanggal_selesai', '>=', $tanggal);
            })
            ->latest('created_at')
            ->get();

        $users = User::query()->where('role', 'user')->orderBy('name')->get();
        $units = Unit::query()->orderBy('nama_unit')->get();

        return view('admin.leave_requests.index', compact('requests', 'users', 'units'));
    }

    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'catatan_admin' => ['nullable', 'string'],
        ]);

        $leaveRequest->update([
            'status' => $validated['status'],
            'catatan_admin' => $validated['catatan_admin'] ?? null,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Status izin berhasil diperbarui.');
    }
}
