<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;

class AttendanceHistoryController extends Controller
{
    public function index(Request $request)
    {
        $histories = Presensi::query()
            ->with(['user.employeeDetail.unit'])
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->user_id))
            ->when($request->filled('tanggal'), fn ($query) => $query->whereDate('tanggal', $request->tanggal))
            ->when($request->filled('unit_id'), function ($query) use ($request) {
                $query->whereHas('user.employeeDetail', fn ($detail) => $detail->where('unit_id', $request->unit_id));
            })
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->get();

        $users = User::query()->where('role', 'user')->orderBy('name')->get();
        $units = Unit::query()->orderBy('nama_unit')->get();

        return view('admin.histories.index', compact('histories', 'users', 'units'));
    }
}
