<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    public function index()
    {
        $unitId = Auth::user()?->employeeDetail?->unit_id;

        $announcements = Announcement::query()
            ->with('unit')
            ->where('is_published', true)
            ->whereDate('tanggal_mulai', '<=', today())
            ->whereDate('tanggal_berakhir', '>=', today())
            ->whereDoesntHave('dismissedByUsers', fn ($users) => $users->whereKey(Auth::id()))
            ->where(function ($query) use ($unitId) {
                $query->where('target_type', 'all');

                if ($unitId) {
                    $query->orWhere(function ($unitQuery) use ($unitId) {
                        $unitQuery->where('target_type', 'unit')->where('unit_id', $unitId);
                    });
                }

                $query->orWhere(function ($userQuery) {
                    $userQuery->where('target_type', 'users')
                        ->whereHas('users', fn ($users) => $users->whereKey(Auth::id()));
                });
            })
            ->latest('tanggal_mulai')
            ->get();

        return view('user.announcements.index', compact('announcements'));
    }

    public function dismiss(Request $request, Announcement $announcement)
    {
        DB::table('announcement_dismissals')->updateOrInsert(
            [
                'announcement_id' => $announcement->id,
                'user_id' => Auth::id(),
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back();
    }
}
