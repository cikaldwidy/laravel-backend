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
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);

                $query->where(function ($q) use ($search) {
                    $q->where('status', 'like', '%' . $search . '%')
                        ->orWhere('status_pulang', 'like', '%' . $search . '%');
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $statusGroups = [
                    'hadir' => ['hadir', 'normal'],
                    'telat' => ['telat', 'terlambat'],
                    'izin' => ['izin'],
                    'pulang_cepat' => ['pulang_cepat'],
                ];

                $statuses = $statusGroups[$request->status] ?? [$request->status];

                $query->where(function ($q) use ($statuses) {
                    $q->whereIn('status', $statuses)
                        ->orWhereIn('status_pulang', $statuses);
                });
            })
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('tanggal', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('tanggal', '<=', $request->date_to))
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->get();

        return view('user.history.index', compact('histories'));
    }
}
