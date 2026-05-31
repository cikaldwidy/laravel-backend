<?php

namespace App\Http\Controllers;

use App\Models\OvertimeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OvertimeController extends Controller
{
    public function user()
    {
        $items = OvertimeRequest::query()
            ->with(['sourceUser', 'leaveRequest'])
            ->where('user_id', Auth::id())
            ->latest('tanggal_mulai')
            ->latest('created_at')
            ->get();

        return view('user.overtime.index', compact('items'));
    }

    public function admin(Request $request)
    {
        $items = OvertimeRequest::query()
            ->with(['user.employeeDetail.department', 'sourceUser', 'leaveRequest', 'approver'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('compensation_type'), fn ($query) => $query->where('compensation_type', $request->compensation_type))
            ->latest('tanggal_mulai')
            ->latest('created_at')
            ->paginate(12)
            ->withQueryString();

        return view('admin.overtime.index', compact('items'));
    }
}
