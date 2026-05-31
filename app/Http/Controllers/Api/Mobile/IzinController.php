<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\FeatureSetting;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\LeavePolicyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class IzinController extends Controller
{
    private const TYPES = ['sakit', 'cuti'];

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->role !== 'user') {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint ini hanya untuk user.',
            ], 403);
        }

        $items = LeaveRequest::query()
            ->where('user_id', $user->id)
            ->whereIn('jenis_izin', self::TYPES)
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->latest('tanggal_mulai')
            ->latest('created_at')
            ->limit(100)
            ->get()
            ->map(fn (LeaveRequest $item) => [
                'id' => $item->id,
                'jenis_izin' => $item->jenis_izin,
                'tanggal_mulai' => $item->tanggal_mulai?->toDateString(),
                'tanggal_selesai' => $item->tanggal_selesai?->toDateString(),
                'keterangan' => $item->keterangan,
                'status' => $item->status,
                'catatan_admin' => $item->catatan_admin,
                'approved_at' => $item->approved_at?->toIso8601String(),
            ])
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Daftar izin berhasil diambil',
            'data' => $items,
        ]);
    }

    public function store(Request $request, LeavePolicyService $leavePolicy): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->role !== 'user') {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint ini hanya untuk user.',
            ], 403);
        }

        $validated = $request->validate([
            'jenis_izin' => ['required', 'string', 'max:50', Rule::in(self::TYPES)],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'keterangan' => ['nullable', 'string'],
        ]);

        if (!FeatureSetting::enabled($validated['jenis_izin'], 'user')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke fitur ini.',
            ], 403);
        }

        if ($validated['jenis_izin'] === 'cuti') {
            try {
                $leavePolicy->validateCutiRequest(
                    $user,
                    $validated['tanggal_mulai'],
                    $validated['tanggal_selesai'] ?? $validated['tanggal_mulai']
                );
            } catch (ValidationException $exception) {
                return response()->json([
                    'success' => false,
                    'message' => collect($exception->errors())->flatten()->first()
                        ?? 'Pengajuan cuti tidak memenuhi aturan.',
                    'errors' => $exception->errors(),
                ], 422);
            }
        }

        $leaveRequest = LeaveRequest::query()->create([
            'user_id' => $user->id,
            'jenis_izin' => $validated['jenis_izin'],
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'] ?? $validated['tanggal_mulai'],
            'keterangan' => $validated['keterangan'] ?? '',
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan izin berhasil dikirim',
            'data' => [
                'id' => $leaveRequest->id,
                'jenis_izin' => $leaveRequest->jenis_izin,
                'tanggal_mulai' => $leaveRequest->tanggal_mulai?->toDateString(),
                'tanggal_selesai' => $leaveRequest->tanggal_selesai?->toDateString(),
                'keterangan' => $leaveRequest->keterangan,
                'status' => $leaveRequest->status,
            ],
        ], 201);
    }
}
