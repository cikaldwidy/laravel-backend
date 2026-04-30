<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $histories = Presensi::query()
            ->where('user_id', Auth::id())
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('tanggal', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('tanggal', '<=', $request->date_to))
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->get();

        return view('user.history.index', compact('histories'));
    }
}
