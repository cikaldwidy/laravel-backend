<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\Presensi;
use App\Models\ShiftSchedule;
use App\Models\User;
use App\Models\WorkSetting;
use App\Support\ShiftTime;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PresensiController extends Controller
{
    public function masuk(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'image' => ['required', 'string'],
            'face_detected' => ['required', 'accepted'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $roleResponse = $this->ensureUser($user);
        if ($roleResponse) {
            return $roleResponse;
        }

        if (!$user->hasFaceEnrollment()) {
            return response()->json([
                'success' => false,
                'message' => 'Wajah belum terdaftar. Selesaikan enrollment terlebih dulu melalui web atau fitur mobile enrollment.',
            ], 422);
        }

        $now = now();
        $setting = WorkSetting::query()->first();
        $activeShiftContext = $this->resolveActiveShift($user, $now, $setting);

        if (!$activeShiftContext) {
            return response()->json([
                'success' => false,
                'message' => 'Shift belum diatur atau Anda berada di luar jam presensi.',
            ], 403);
        }

        $tanggalPresensi = $activeShiftContext['shift_date']->toDateString();
        $approvedLeave = $this->getApprovedLeaveForDate($user->id, $tanggalPresensi);

        if ($approvedLeave) {
            $presensi = Presensi::query()->updateOrCreate(
                ['user_id' => $user->id, 'tanggal' => $tanggalPresensi],
                ['status' => 'izin', 'status_pulang' => null]
            );

            return response()->json([
                'success' => false,
                'message' => 'Anda memiliki izin yang sudah disetujui pada tanggal presensi ini.',
                'data' => $this->formatPresensi($presensi),
            ], 409);
        }

        $distanceResponse = $this->validateOfficeDistance($validated, $setting);
        if ($distanceResponse instanceof JsonResponse) {
            return $distanceResponse;
        }

        $presensi = Presensi::query()
            ->where('user_id', $user->id)
            ->whereDate('tanggal', $tanggalPresensi)
            ->first();

        if ($presensi?->jam_masuk) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan presensi masuk.',
                'data' => $this->formatPresensi($presensi),
            ], 409);
        }

        $statusMasuk = $now->lte($activeShiftContext['start']) ? 'hadir' : 'terlambat';
        $photoPath = $this->storeAttendanceImage($validated['image'], $user->id);

        $presensi = Presensi::query()->updateOrCreate(
            ['user_id' => $user->id, 'tanggal' => $tanggalPresensi],
            [
                'jam_masuk' => $now,
                'foto' => $photoPath,
                'foto_masuk' => $photoPath,
                'latitude_masuk' => $validated['lat'],
                'longitude_masuk' => $validated['lng'],
                'jarak_masuk' => round($distanceResponse, 2),
                'status' => $statusMasuk,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Presensi masuk berhasil',
            'data' => $this->formatPresensi($presensi),
        ]);
    }

    public function pulang(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'image' => ['required', 'string'],
            'face_detected' => ['required', 'accepted'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $roleResponse = $this->ensureUser($user);
        if ($roleResponse) {
            return $roleResponse;
        }

        if (!$user->hasFaceEnrollment()) {
            return response()->json([
                'success' => false,
                'message' => 'Wajah belum terdaftar. Selesaikan enrollment terlebih dulu melalui web atau fitur mobile enrollment.',
            ], 422);
        }

        $now = now();
        $setting = WorkSetting::query()->first();
        $activeShiftContext = $this->resolveActiveShift($user, $now, $setting);

        if (!$activeShiftContext) {
            return response()->json([
                'success' => false,
                'message' => 'Shift belum diatur atau Anda berada di luar jam presensi.',
            ], 403);
        }

        $tanggalPresensi = $activeShiftContext['shift_date']->toDateString();
        $presensi = Presensi::query()
            ->where('user_id', $user->id)
            ->whereDate('tanggal', $tanggalPresensi)
            ->first();

        if (!$presensi?->jam_masuk) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum melakukan presensi masuk.',
            ], 422);
        }

        if ($presensi->jam_keluar) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan presensi pulang.',
                'data' => $this->formatPresensi($presensi),
            ], 409);
        }

        if ($now->lt($activeShiftContext['end'])) {
            return response()->json([
                'success' => false,
                'message' => 'Belum waktunya pulang.',
            ], 422);
        }

        $distanceResponse = $this->validateOfficeDistance($validated, $setting);
        if ($distanceResponse instanceof JsonResponse) {
            return $distanceResponse;
        }

        $photoPath = $this->storeAttendanceImage($validated['image'], $user->id);

        $presensi->update([
            'jam_keluar' => $now,
            'foto_keluar' => $photoPath,
            'latitude_keluar' => $validated['lat'],
            'longitude_keluar' => $validated['lng'],
            'jarak_keluar' => round($distanceResponse, 2),
            'status_pulang' => 'normal',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Presensi pulang berhasil',
            'data' => $this->formatPresensi($presensi->fresh()),
        ]);
    }

    public function riwayat(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $roleResponse = $this->ensureUser($user);
        if ($roleResponse) {
            return $roleResponse;
        }

        $histories = Presensi::query()
            ->where('user_id', $user->id)
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where(function ($inner) use ($request) {
                    $inner->where('status', $request->status)
                        ->orWhere('status_pulang', $request->status);
                });
            })
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('tanggal', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('tanggal', '<=', $request->date_to))
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get()
            ->map(fn (Presensi $presensi) => $this->formatPresensi($presensi))
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Riwayat presensi berhasil diambil',
            'data' => $histories,
        ]);
    }

    private function ensureUser(User $user): ?JsonResponse
    {
        if ($user->role === 'user') {
            return null;
        }

        return response()->json([
            'success' => false,
            'message' => 'Endpoint ini hanya untuk user.',
        ], 403);
    }

    private function resolveActiveShift(User $user, Carbon $now, ?WorkSetting $setting = null): ?array
    {
        $checkinEarlyMinutes = (int) ($setting?->checkin_early_minutes ?? WorkSetting::DEFAULT_CHECKIN_EARLY_MINUTES);
        $checkoutLateMinutes = (int) ($setting?->checkout_late_minutes ?? WorkSetting::DEFAULT_CHECKOUT_LATE_MINUTES);

        $shiftCandidates = ShiftSchedule::query()
            ->where('user_id', $user->id)
            ->where('status', 'aktif')
            ->whereIn('tanggal', [
                $now->toDateString(),
                $now->copy()->subDay()->toDateString(),
            ])
            ->orderByDesc('tanggal')
            ->get();

        foreach ($shiftCandidates as $candidate) {
            $shiftDate = Carbon::parse($candidate->tanggal)->startOfDay();
            $window = ShiftTime::window(
                $shiftDate,
                $candidate->jam_masuk->format('H:i:s'),
                $candidate->jam_pulang->format('H:i:s'),
                $checkinEarlyMinutes,
                $checkoutLateMinutes
            );

            if (!$now->between($window['allowed_start'], $window['allowed_end'], true)) {
                continue;
            }

            return [
                'shift' => $candidate,
                'shift_date' => $shiftDate,
                'start' => $window['start'],
                'end' => $window['end'],
            ];
        }

        return null;
    }

    private function getApprovedLeaveForDate(int $userId, string $tanggal): ?LeaveRequest
    {
        return LeaveRequest::query()
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->whereDate('tanggal_mulai', '<=', $tanggal)
            ->whereDate('tanggal_selesai', '>=', $tanggal)
            ->latest('approved_at')
            ->first();
    }

    private function validateOfficeDistance(array $validated, ?WorkSetting $setting): float|JsonResponse
    {
        $officeLatitude = (float) ($setting?->office_latitude ?? config('attendance.office_latitude', -6.123456));
        $officeLongitude = (float) ($setting?->office_longitude ?? config('attendance.office_longitude', 106.123456));
        $officeRadius = (int) ($setting?->radius_meters ?? config('attendance.radius_meters', 100));

        $distance = $this->calculateDistance(
            (float) $validated['lat'],
            (float) $validated['lng'],
            $officeLatitude,
            $officeLongitude
        );

        if ($distance > $officeRadius) {
            return response()->json([
                'success' => false,
                'message' => 'Anda berada di luar radius kantor (' . round($distance) . ' meter).',
                'data' => [
                    'distance_meters' => round($distance, 2),
                    'allowed_radius_meters' => $officeRadius,
                ],
            ], 403);
        }

        return $distance;
    }

    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function storeAttendanceImage(string $imageData, int $userId): string
    {
        if (!preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
            $this->rejectImage('Format foto wajah tidak valid.');
        }

        $extension = strtolower($matches[1]);
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $this->rejectImage('Format foto wajah harus jpg, jpeg, png, atau webp.');
        }

        $image = substr($imageData, strpos($imageData, ',') + 1);
        $decoded = base64_decode(str_replace(' ', '+', $image), true);

        if ($decoded === false) {
            $this->rejectImage('Foto wajah gagal dibaca.');
        }

        $fileName = "attendance/$userId/" . Str::uuid() . ".$extension";
        Storage::disk('public')->put($fileName, $decoded);

        return $fileName;
    }

    private function rejectImage(string $message): never
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $message,
        ], 422));
    }

    private function formatPresensi(?Presensi $presensi): ?array
    {
        if (!$presensi) {
            return null;
        }

        return [
            'id' => $presensi->id,
            'tanggal' => $presensi->tanggal?->toDateString(),
            'jam_masuk' => $presensi->jam_masuk?->format('H:i'),
            'jam_keluar' => $presensi->jam_keluar?->format('H:i'),
            'status' => $presensi->status,
            'status_pulang' => $presensi->status_pulang,
            'latitude_masuk' => $presensi->latitude_masuk,
            'longitude_masuk' => $presensi->longitude_masuk,
            'latitude_keluar' => $presensi->latitude_keluar,
            'longitude_keluar' => $presensi->longitude_keluar,
            'jarak_masuk' => $presensi->jarak_masuk,
            'jarak_keluar' => $presensi->jarak_keluar,
            'keterangan' => $presensi->status_pulang ?: $presensi->status,
        ];
    }
}
