<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
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
            ->where(function ($query) use ($unitId) {
                $query->where('target_type', 'all');

                if ($unitId) {
                    $query->orWhere(function ($unitQuery) use ($unitId) {
                        $unitQuery->where('target_type', 'unit')->where('unit_id', $unitId);
                    });
                }
            })
            ->latest('tanggal_mulai')
            ->get();

        return view('user.announcements.index', compact('announcements'));
    }
}
