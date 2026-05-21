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
            'location_accuracy' => ['required', 'numeric', 'min:0', 'max:1000'],
            'location_timestamp' => ['required', 'date'],
            'location_age_seconds' => ['required', 'numeric', 'min:0', 'max:300'],
            'is_mocked' => ['required', 'boolean'],
            'location_samples' => ['required', 'array', 'min:3', 'max:5'],
            'location_samples.*.latitude' => ['required', 'numeric', 'between:-90,90'],
            'location_samples.*.longitude' => ['required', 'numeric', 'between:-180,180'],
            'location_samples.*.accuracy' => ['required', 'numeric', 'min:0', 'max:1000'],
            'location_samples.*.timestamp' => ['required', 'date'],
            'location_samples.*.age_seconds' => ['required', 'numeric', 'min:0', 'max:300'],
            'location_samples.*.is_mocked' => ['nullable', 'boolean'],
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

        $distanceResponse = $this->validateTrustedLocation($validated, $setting);
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
            'location_accuracy' => ['required', 'numeric', 'min:0', 'max:1000'],
            'location_timestamp' => ['required', 'date'],
            'location_age_seconds' => ['required', 'numeric', 'min:0', 'max:300'],
            'is_mocked' => ['required', 'boolean'],
            'location_samples' => ['required', 'array', 'min:3', 'max:5'],
            'location_samples.*.latitude' => ['required', 'numeric', 'between:-90,90'],
            'location_samples.*.longitude' => ['required', 'numeric', 'between:-180,180'],
            'location_samples.*.accuracy' => ['required', 'numeric', 'min:0', 'max:1000'],
            'location_samples.*.timestamp' => ['required', 'date'],
            'location_samples.*.age_seconds' => ['required', 'numeric', 'min:0', 'max:300'],
            'location_samples.*.is_mocked' => ['nullable', 'boolean'],
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

        $distanceResponse = $this->validateTrustedLocation($validated, $setting);
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

    public function face(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'type' => ['nullable', 'in:masuk,pulang'],
            'embedding' => ['required', 'array', 'size:128'],
            'embedding.*' => ['required', 'numeric'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'location_accuracy' => ['required', 'numeric', 'min:0', 'max:1000'],
            'is_mocked' => ['required', 'boolean'],
            'location_timestamp' => ['required', 'date'],
            'location_age_seconds' => ['required', 'numeric', 'min:0', 'max:300'],
            'location_samples' => ['required', 'array', 'min:3', 'max:5'],
            'location_samples.*.latitude' => ['required', 'numeric', 'between:-90,90'],
            'location_samples.*.longitude' => ['required', 'numeric', 'between:-180,180'],
            'location_samples.*.accuracy' => ['required', 'numeric', 'min:0', 'max:1000'],
            'location_samples.*.timestamp' => ['required', 'date'],
            'location_samples.*.age_seconds' => ['required', 'numeric', 'min:0', 'max:300'],
            'location_samples.*.is_mocked' => ['nullable', 'boolean'],
            'timestamp' => ['nullable', 'date'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $roleResponse = $this->ensureUser($user);
        if ($roleResponse) {
            return $roleResponse;
        }

        if (isset($validated['user_id']) && (int) $validated['user_id'] !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'User absensi tidak sesuai dengan token login.',
            ], 403);
        }

        if (!$user->hasFaceEnrollment()) {
            return response()->json([
                'success' => false,
                'message' => 'Wajah belum terdaftar. Selesaikan enrollment terlebih dulu.',
            ], 422);
        }

        $incomingEmbedding = array_map('floatval', $validated['embedding']);
        $storedEmbedding = $user->faceEmbedding?->embedding;
        if (!is_array($storedEmbedding) || count($storedEmbedding) !== 128) {
            return response()->json([
                'success' => false,
                'message' => 'Data embedding wajah terdaftar belum valid. Hubungi admin untuk enrollment ulang.',
            ], 422);
        }

        $faceDistance = $this->compareEmbeddings($storedEmbedding, $incomingEmbedding);
        if ($faceDistance > (float) config('attendance.face_threshold', 0.65)) {
            return response()->json([
                'success' => false,
                'message' => 'Wajah tidak cocok dengan data yang terdaftar.',
                'data' => [
                    'face_distance' => round($faceDistance, 6),
                ],
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

        $latitude = (float) $validated['latitude'];
        $longitude = (float) $validated['longitude'];
        $distanceResponse = $this->validateTrustedLocation([
            'lat' => $latitude,
            'lng' => $longitude,
            'location_accuracy' => $validated['location_accuracy'],
            'location_timestamp' => $validated['location_timestamp'],
            'location_age_seconds' => $validated['location_age_seconds'],
            'is_mocked' => $validated['is_mocked'],
            'location_samples' => $validated['location_samples'],
        ], $setting);
        if ($distanceResponse instanceof JsonResponse) {
            return $distanceResponse;
        }

        $presensi = Presensi::query()
            ->where('user_id', $user->id)
            ->whereDate('tanggal', $tanggalPresensi)
            ->first();
        $type = $validated['type'] ?? ((!$presensi || !$presensi->jam_masuk) ? 'masuk' : 'pulang');

        if ($type === 'masuk') {
            if ($presensi?->jam_masuk) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan presensi masuk.',
                    'data' => $this->formatPresensi($presensi),
                ], 409);
            }

            $statusMasuk = $now->lte($activeShiftContext['start']) ? 'hadir' : 'terlambat';
            $presensi = Presensi::query()->updateOrCreate(
                ['user_id' => $user->id, 'tanggal' => $tanggalPresensi],
                [
                    'jam_masuk' => $now,
                    'latitude_masuk' => $latitude,
                    'longitude_masuk' => $longitude,
                    'jarak_masuk' => $distanceResponse !== null ? round($distanceResponse, 2) : null,
                    'face_distance_masuk' => round($faceDistance, 6),
                    'embedding_absensi' => $incomingEmbedding,
                    'embedding_masuk' => $incomingEmbedding,
                    'status' => $statusMasuk,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Absensi berhasil disimpan',
                'data' => array_merge($this->formatPresensi($presensi), [
                    'type' => 'masuk',
                    'face_distance' => round($faceDistance, 6),
                ]),
            ]);
        }

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

        $presensi->update([
            'jam_keluar' => $now,
            'latitude_keluar' => $latitude,
            'longitude_keluar' => $longitude,
            'jarak_keluar' => $distanceResponse !== null ? round($distanceResponse, 2) : null,
            'face_distance_keluar' => round($faceDistance, 6),
            'embedding_absensi' => $incomingEmbedding,
            'embedding_keluar' => $incomingEmbedding,
            'status_pulang' => 'normal',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absensi berhasil disimpan',
            'data' => array_merge($this->formatPresensi($presensi->fresh()), [
                'type' => 'pulang',
                'face_distance' => round($faceDistance, 6),
            ]),
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

    private function validateTrustedLocation(array $validated, ?WorkSetting $setting): float|JsonResponse
    {
        if ((bool) ($validated['is_mocked'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => 'Lokasi terdeteksi berasal dari mock location atau fake GPS.',
            ], 403);
        }

        $metadataResponse = $this->validateLocationMetadata($validated);
        if ($metadataResponse instanceof JsonResponse) {
            return $metadataResponse;
        }

        return $this->validateOfficeDistance($validated, $setting);
    }

    private function validateLocationMetadata(array $validated): ?JsonResponse
    {
        $maxLocationAccuracy = (float) config('attendance.max_location_accuracy_meters', 80);
        if ((float) $validated['location_accuracy'] > $maxLocationAccuracy) {
            return response()->json([
                'success' => false,
                'message' => 'Akurasi lokasi terlalu rendah. Dekatkan perangkat ke area terbuka lalu coba lagi.',
                'data' => [
                    'location_accuracy' => round((float) $validated['location_accuracy'], 2),
                    'max_location_accuracy' => $maxLocationAccuracy,
                ],
            ], 422);
        }

        $maxLocationAge = (float) config('attendance.max_location_age_seconds', 20);
        if ((float) $validated['location_age_seconds'] > $maxLocationAge) {
            return response()->json([
                'success' => false,
                'message' => 'Data lokasi sudah terlalu lama. Ambil GPS ulang lalu coba lagi.',
                'data' => [
                    'location_age_seconds' => round((float) $validated['location_age_seconds'], 2),
                    'max_location_age_seconds' => $maxLocationAge,
                ],
            ], 422);
        }

        try {
            $locationTimestamp = Carbon::parse($validated['location_timestamp']);
        } catch (\Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Timestamp lokasi tidak valid.',
            ], 422);
        }

        $maxClientTimeSkew = (int) config('attendance.max_client_time_skew_seconds', 120);
        $timeSkew = abs(now()->diffInSeconds($locationTimestamp, false));
        if ($timeSkew > $maxClientTimeSkew) {
            return response()->json([
                'success' => false,
                'message' => 'Waktu perangkat tidak sinkron. Periksa jam perangkat lalu coba lagi.',
                'data' => [
                    'client_time_skew_seconds' => $timeSkew,
                    'max_client_time_skew_seconds' => $maxClientTimeSkew,
                ],
            ], 422);
        }

        return $this->validateLocationSamples(
            $validated['location_samples'] ?? [],
            (float) $validated['lat'],
            (float) $validated['lng']
        );
    }

    private function validateLocationSamples(array $samples, float $latitude, float $longitude): ?JsonResponse
    {
        $requiredSamples = (int) config('attendance.required_location_samples', 3);
        if (count($samples) < $requiredSamples) {
            return response()->json([
                'success' => false,
                'message' => 'Sampel lokasi belum cukup. Tunggu GPS stabil lalu coba lagi.',
                'data' => [
                    'received_location_samples' => count($samples),
                    'required_location_samples' => $requiredSamples,
                ],
            ], 422);
        }

        $maxLocationAccuracy = (float) config('attendance.max_location_accuracy_meters', 80);
        $maxLocationAge = (float) config('attendance.max_location_age_seconds', 20);
        $maxClientTimeSkew = (int) config('attendance.max_client_time_skew_seconds', 120);
        $maxSampleSpread = (float) config('attendance.max_location_sample_spread_meters', 35);

        foreach ($samples as $sample) {
            if ((bool) ($sample['is_mocked'] ?? false)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sampel lokasi terdeteksi berasal dari mock location atau fake GPS.',
                ], 403);
            }

            if ((float) $sample['accuracy'] > $maxLocationAccuracy) {
                return response()->json([
                    'success' => false,
                    'message' => 'Salah satu sampel lokasi terlalu tidak akurat. Ambil GPS ulang lalu coba lagi.',
                ], 422);
            }

            if ((float) $sample['age_seconds'] > $maxLocationAge) {
                return response()->json([
                    'success' => false,
                    'message' => 'Salah satu sampel lokasi sudah kedaluwarsa. Ambil GPS ulang lalu coba lagi.',
                ], 422);
            }

            try {
                $sampleTimestamp = Carbon::parse($sample['timestamp']);
            } catch (\Throwable) {
                return response()->json([
                    'success' => false,
                    'message' => 'Timestamp sampel lokasi tidak valid.',
                ], 422);
            }

            $timeSkew = abs(now()->diffInSeconds($sampleTimestamp, false));
            if ($timeSkew > $maxClientTimeSkew) {
                return response()->json([
                    'success' => false,
                    'message' => 'Waktu sampel lokasi tidak sinkron dengan server.',
                ], 422);
            }

            $distanceFromClaim = $this->calculateDistance(
                (float) $sample['latitude'],
                (float) $sample['longitude'],
                $latitude,
                $longitude
            );

            if ($distanceFromClaim > $maxSampleSpread) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pergerakan lokasi terlalu besar. Tunggu GPS stabil lalu coba lagi.',
                    'data' => [
                        'sample_spread_meters' => round($distanceFromClaim, 2),
                        'max_location_sample_spread_meters' => $maxSampleSpread,
                    ],
                ], 422);
            }
        }

        return null;
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

    private function compareEmbeddings(array $storedEmbedding, array $incomingEmbedding): float
    {
        $sum = 0.0;
        foreach ($storedEmbedding as $index => $value) {
            $diff = (float) $value - (float) $incomingEmbedding[$index];
            $sum += $diff * $diff;
        }

        return sqrt($sum);
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
            'has_embedding_absensi' => is_array($presensi->embedding_absensi),
            'keterangan' => $presensi->status_pulang ?: $presensi->status,
        ];
    }
}
