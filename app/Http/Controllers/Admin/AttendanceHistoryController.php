<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Models\Unit;
use Illuminate\Http\Request;

class AttendanceHistoryController extends Controller
{
    public function index(Request $request)
    {
        $histories = Presensi::query()
            ->with(['user.employeeDetail.unit'])
            ->when($request->filled('tanggal'), fn ($query) => $query->whereDate('tanggal', $request->tanggal))
            ->when($request->filled('unit_id'), function ($query) use ($request) {
                $query->whereHas('user.employeeDetail', fn ($detail) => $detail->where('unit_id', $request->unit_id));
            }, function ($query) {
                $query->whereRaw('1 = 0');
            })
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->get();

        $units = Unit::query()->orderBy('nama_unit')->get();

        return view('admin.histories.index', compact('histories', 'units'));
    }
}
