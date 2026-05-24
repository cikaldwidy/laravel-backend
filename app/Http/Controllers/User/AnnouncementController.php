<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = $this->visibleAnnouncementsQuery(Auth::user())
            ->latest('tanggal_mulai')
            ->get();

        return view('user.announcements.index', compact('announcements'));
    }

    public function feed(): JsonResponse
    {
        $user = Auth::user();
        $baseQuery = $this->visibleAnnouncementsQuery($user);
        $count = (clone $baseQuery)->count();

        $announcements = (clone $baseQuery)
            ->latest('tanggal_mulai')
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(fn (Announcement $announcement) => [
                'id' => $announcement->id,
                'title' => $announcement->judul,
                'body' => $announcement->isi,
                'date_range' => $announcement->tanggal_mulai->format('d/m/Y') . ' - ' . $announcement->tanggal_berakhir->format('d/m/Y'),
                'target_type' => $announcement->target_type,
                'target_label' => $announcement->target_type === 'users' ? 'Khusus' : null,
                'action_url' => $announcement->action_url,
                'dismiss_url' => route('announcements.dismiss', $announcement, false),
                'updated_at' => optional($announcement->updated_at)->toIso8601String(),
            ]);

        return response()->json([
            'count' => $count,
            'announcements' => $announcements,
        ]);
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

    private function visibleAnnouncementsQuery(?User $user)
    {
        $unitId = $user?->employeeDetail?->unit_id;
        $userId = $user?->id;

        return Announcement::query()
            ->with('unit')
            ->where('is_published', true)
            ->whereDate('tanggal_mulai', '<=', today())
            ->whereDate('tanggal_berakhir', '>=', today())
            ->whereDoesntHave('dismissedByUsers', fn ($users) => $users->whereKey($userId))
            ->where(function ($query) use ($unitId, $userId) {
                $query->where('target_type', 'all');

                if ($unitId) {
                    $query->orWhere(function ($unitQuery) use ($unitId) {
                        $unitQuery->where('target_type', 'unit')->where('unit_id', $unitId);
                    });
                }

                $query->orWhere(function ($userQuery) use ($userId) {
                    $userQuery->where('target_type', 'users')
                        ->whereHas('users', fn ($users) => $users->whereKey($userId));
                });
            });
    }
}
