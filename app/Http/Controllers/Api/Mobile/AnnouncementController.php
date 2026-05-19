<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user()->loadMissing('employeeDetail.unit');

        if ($user->role !== 'user') {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint ini hanya untuk user.',
            ], 403);
        }

        $unitId = $user->employeeDetail?->unit_id;

        $items = Announcement::query()
            ->with('unit')
            ->where('is_published', true)
            ->whereDate('tanggal_mulai', '<=', today())
            ->whereDate('tanggal_berakhir', '>=', today())
            ->whereDoesntHave('dismissedByUsers', fn ($users) => $users->whereKey($user->id))
            ->where(function ($query) use ($unitId, $user) {
                $query->where('target_type', 'all');

                if ($unitId) {
                    $query->orWhere(function ($unitQuery) use ($unitId) {
                        $unitQuery->where('target_type', 'unit')->where('unit_id', $unitId);
                    });
                }

                $query->orWhere(function ($userQuery) use ($user) {
                    $userQuery->where('target_type', 'users')
                        ->whereHas('users', fn ($users) => $users->whereKey($user->id));
                });
            })
            ->latest('tanggal_mulai')
            ->get()
            ->map(fn (Announcement $item) => [
                'id' => $item->id,
                'judul' => $item->judul,
                'isi' => $item->isi,
                'target_type' => $item->target_type,
                'unit' => $item->unit?->nama_unit,
                'tanggal_mulai' => $item->tanggal_mulai?->toDateString(),
                'tanggal_berakhir' => $item->tanggal_berakhir?->toDateString(),
            ])
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Pengumuman berhasil diambil',
            'data' => $items,
        ]);
    }
}
