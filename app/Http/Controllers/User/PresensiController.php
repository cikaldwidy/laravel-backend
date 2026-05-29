<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\FaceEmbedding;
use App\Models\Presensi;
use App\Models\LeaveRequest;
use App\Models\ShiftSchedule;
use App\Models\User;
use App\Models\WorkSetting;
use App\Support\IpNetwork;
use App\Support\ShiftTime;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PresensiController extends Controller
{
    public function show()
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->hasFaceEnrollment()) {
            return redirect()->route('face.enroll');
        }

        $now = now();
        $setting = WorkSetting::first();
        $activeShiftContext = $this->resolveActiveShift($user, $now, $setting);
        $scheduledShift = $this->getTodayShiftAssignment($user, $now);
        $activeShift = $activeShiftContext['shift'] ?? null;
        $canAttend = (bool) $activeShiftContext;
        $tanggalPresensi = $activeShiftContext['shift_date'] ?? $now->copy()->startOfDay();
        $approvedLeave = $this->getApprovedLeaveForDate($user->id, $tanggalPresensi->toDateString());

        $presensi = Presensi::where('user_id', $user->id)
            ->whereDate('tanggal', $tanggalPresensi)
            ->first();

        return view('user.absen', [
            'presensi' => $presensi,
            'workSetting' => $setting,
            'activeShift' => $activeShift,
            'scheduledShift' => $scheduledShift,
            'canAttend' => $canAttend,
            'approvedLeave' => $approvedLeave,
            'faceThreshold' => config('attendance.face_threshold', 0.35),
            'officeRadius' => $setting->radius_meters ?? config('attendance.radius_meters', 100),
            'officeLatitude' => $setting->office_latitude ?? config('attendance.office_latitude'),
            'officeLongitude' => $setting->office_longitude ?? config('attendance.office_longitude'),
        ]);
    }

    public function status(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->hasFaceEnrollment()) {
            return response()->json([
                'has_face_enrollment' => false,
                'has_attendance' => false,
                'redirect' => route('face.enroll', [], false),
            ]);
        }

        $now = now();
        $setting = WorkSetting::first();
        $activeShiftContext = $this->resolveActiveShift($user, $now, $setting);
        $tanggalPresensi = $activeShiftContext['shift_date'] ?? $now->copy()->startOfDay();

        $presensi = Presensi::query()
            ->where('user_id', $user->id)
            ->whereDate('tanggal', $tanggalPresensi)
            ->first();

        return response()->json([
            'has_face_enrollment' => true,
            'has_attendance' => (bool) $presensi?->jam_masuk,
            'has_checkout' => (bool) $presensi?->jam_keluar,
            'redirect' => route('dashboard', [], false),
        ]);
    }

    public function absen(Request $request)
    {
        $validated = $request->validate([
            'image' => ['required', 'string'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'location_accuracy' => ['required', 'numeric', 'min:0', 'max:1000'],
            'location_timestamp' => ['required', 'date'],
            'location_age_seconds' => ['required', 'numeric', 'min:0', 'max:300'],
            'location_samples' => ['required', 'array', 'min:1', 'max:5'],
            'location_samples.*.latitude' => ['required', 'numeric', 'between:-90,90'],
            'location_samples.*.longitude' => ['required', 'numeric', 'between:-180,180'],
            'location_samples.*.accuracy' => ['required', 'numeric', 'min:0', 'max:1000'],
            'location_samples.*.timestamp' => ['required', 'date'],
            'location_samples.*.age_seconds' => ['required', 'numeric', 'min:0', 'max:300'],
            'embedding' => ['required', 'array', 'size:128'],
            'embedding.*' => ['required', 'numeric'],
            'descriptor_samples' => ['required', 'array', 'min:3', 'max:5'],
            'descriptor_samples.*' => ['required', 'array', 'size:128'],
            'descriptor_samples.*.*' => ['required', 'numeric'],
            'quality_metrics' => ['required', 'array'],
            'quality_metrics.brightness' => ['required', 'numeric'],
            'quality_metrics.sharpness' => ['required', 'numeric'],
            'blink_verified' => ['required', 'accepted'],
        ]);

        /** @var User $user */
        $user = Auth::user();

        if (!$user->hasFaceEnrollment()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Wajah belum terdaftar. Selesaikan enrollment terlebih dulu.',
            ], 422);
        }

        $setting = WorkSetting::first();
        $officeLatitude = (float) ($setting->office_latitude ?? config('attendance.office_latitude', -6.123456));
        $officeLongitude = (float) ($setting->office_longitude ?? config('attendance.office_longitude', 106.123456));
        $officeRadius = (int) ($setting->radius_meters ?? config('attendance.radius_meters', 100));

        $networkResponse = $this->validateAttendanceNetwork($request, $setting);
        if ($networkResponse instanceof \Illuminate\Http\JsonResponse) {
            return $networkResponse;
        }

        if (!$this->passesQualityGate($validated['quality_metrics'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Wajah tidak cukup jelas. Ulangi scan di tempat yang lebih terang dan stabil.',
            ], 422);
        }

        $distance = $this->validateTrustedLocation($validated, $setting);
        if ($distance instanceof \Illuminate\Http\JsonResponse) {
            return $distance;
        }

        $storedEmbedding = $user->faceEmbedding?->embedding;
        $storedDescriptorSamples = $user->faceEmbedding?->descriptor_samples;

        if (!is_array($storedEmbedding) || count($storedEmbedding) !== 128) {
            return response()->json([
                'status' => 'error',
                'message' => 'Template wajah user tidak valid. Silakan daftar ulang wajah.',
            ], 422);
        }

        if (!$this->hasValidDescriptorSamples($storedDescriptorSamples)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data pendaftaran wajah belum lengkap. Silakan daftar ulang wajah.',
            ], 422);
        }

        $faceDistance = $this->compareEmbeddings($storedEmbedding, $validated['embedding']);
        $registrationSampleDistance = $this->findClosestDescriptorDistance(
            $storedDescriptorSamples,
            $validated['embedding']
        );
        $sampleDistances = array_map(
            fn (array $sample) => $this->compareEmbeddings($storedEmbedding, $sample),
            $validated['descriptor_samples']
        );
        $sampleRegistrationDistances = array_map(
            fn (array $sample) => $this->findClosestDescriptorDistance($storedDescriptorSamples, $sample),
            $validated['descriptor_samples']
        );
        $worstSampleDistance = max($sampleDistances);
        $worstRegistrationSampleDistance = max($sampleRegistrationDistances);
        $faceThreshold = (float) config('attendance.face_threshold');

        if (
            $faceDistance > $faceThreshold ||
            $registrationSampleDistance > $faceThreshold ||
            $worstSampleDistance > $faceThreshold ||
            $worstRegistrationSampleDistance > $faceThreshold
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Wajah tidak sama dengan data pendaftaran.',
                'face_distance' => round($faceDistance, 6),
            ], 422);
        }

        $closestOtherFaceDistance = $this->findClosestOtherFaceDistance($user->id, $validated['embedding']);

        if ($closestOtherFaceDistance !== null && $closestOtherFaceDistance < $faceDistance) {
            return response()->json([
                'status' => 'error',
                'message' => 'Wajah tidak sama dengan akun yang sedang login.',
                'face_distance' => round($faceDistance, 6),
            ], 422);
        }

        $now = now();
        $activeShiftContext = $this->resolveActiveShift($user, $now, $setting);

        if (!$activeShiftContext) {
            return response()->json([
                'status' => 'error',
                'message' => 'Shift belum diatur untuk Anda atau Anda berada di luar jam shift.',
            ], 403);
        }

        // Tanggal presensi mengikuti tanggal shift agar shift malam setelah tengah malam tetap dianggap shift kemarin.
        $tanggalPresensi = $activeShiftContext['shift_date']->toDateString();
        $approvedLeave = $this->getApprovedLeaveForDate($user->id, $tanggalPresensi);

        if ($approvedLeave) {
            Presensi::updateOrCreate(
                ['user_id' => $user->id, 'tanggal' => $tanggalPresensi],
                [
                    'status' => 'izin',
                    'status_pulang' => null,
                ]
            );

            return response()->json([
                'status' => 'izin',
                'message' => 'Anda memiliki izin yang sudah disetujui hari ini.',
                'redirect' => route('dashboard', [], false),
            ], 409);
        }

        $jamMasukShift = $activeShiftContext['start'];
        $jamPulangShift = $activeShiftContext['end'];

        $presensi = Presensi::where('user_id', $user->id)
            ->whereDate('tanggal', $tanggalPresensi)
            ->first();

        $photoPath = $this->storeAttendanceImage($validated['image'], $user->id);
        $livenessPayload = [
            'type' => 'blink',
            'blink_verified' => true,
            'verified_at' => now()->toIso8601String(),
        ];

        // CHECK-IN
        if (!$presensi) {
            $statusMasuk = $now->lte($jamMasukShift) ? 'hadir' : 'terlambat';

            Presensi::create([
                'user_id' => $user->id,
                'tanggal' => $tanggalPresensi,
                'jam_masuk' => now(),
                'foto' => $photoPath,
                'foto_masuk' => $photoPath,
                'latitude_masuk' => $validated['lat'],
                'longitude_masuk' => $validated['lng'],
                'jarak_masuk' => round($distance, 2),
                'face_distance_masuk' => round($faceDistance, 6),
                'liveness_challenge' => $livenessPayload,
                'status' => $statusMasuk,
            ]);

            return response()->json([
                'status' => 'masuk',
                'message' => 'Absen masuk berhasil',
                'status_presensi' => $statusMasuk,
                'redirect' => route('dashboard', [], false),
            ]);
        }

        // VALIDASI TAMBAHAN
        if (!$presensi->jam_masuk) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda belum melakukan absen masuk.',
            ], 422);
        }

        if ($presensi->jam_keluar) {
            return response()->json([
                'status' => 'done',
                'message' => 'Anda sudah melakukan absen masuk dan pulang hari ini',
            ], 409);
        }

        if ($now->lt($jamPulangShift)) {
            return response()->json([
                'status' => 'error',
                'message' => 'belum waktunya pulang',
            ], 422);
        }

        // CHECK-OUT
        $presensi->update([
            'jam_keluar' => now(),
            'foto_keluar' => $photoPath,
            'latitude_keluar' => $validated['lat'],
            'longitude_keluar' => $validated['lng'],
            'jarak_keluar' => round($distance, 2),
            'face_distance_keluar' => round($faceDistance, 6),
            'liveness_challenge' => $livenessPayload,
            'status_pulang' => 'normal',
        ]);

        return response()->json([
            'status' => 'pulang',
            'message' => 'Absen pulang berhasil',
            'status_pulang' => 'normal',
            'redirect' => route('dashboard', [], false),
        ]);
    }

    private function getTodayShiftAssignment(User $user, Carbon $now): ?ShiftSchedule
    {
        return ShiftSchedule::query()
            ->where('user_id', $user->id)
            ->whereDate('tanggal', $now->toDateString())
            ->first();
    }

    private function resolveActiveShift(User $user, Carbon $now, ?WorkSetting $setting = null): ?array
    {
        $checkinEarlyMinutes = (int) ($setting?->checkin_early_minutes ?? WorkSetting::DEFAULT_CHECKIN_EARLY_MINUTES);
        $checkoutLateMinutes = (int) ($setting?->checkout_late_minutes ?? WorkSetting::DEFAULT_CHECKOUT_LATE_MINUTES);

        // Cek shift hari ini dan shift kemarin untuk handle shift lintas hari.
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
            $jamMasuk = $candidate->jam_masuk->format('H:i:s');
            $jamPulang = $candidate->jam_pulang->format('H:i:s');
            $window = ShiftTime::window($shiftDate, $jamMasuk, $jamPulang, $checkinEarlyMinutes, $checkoutLateMinutes);

            if (!$now->between($window['allowed_start'], $window['allowed_end'], true)) {
                continue;
            }

            return [
                'assignment' => $candidate,
                'shift' => $candidate,
                'shift_date' => $shiftDate,
                'start' => $window['start'],
                'end' => $window['end'],
                'is_overnight' => ShiftTime::isOvernight($jamMasuk, $jamPulang),
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

    // ================= HELPER =================

    private function storeAttendanceImage(string $imageData, int $userId): string
    {
        if (!preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
            $this->rejectRequest('Format gambar tidak valid.');
        }

        $extension = strtolower($matches[1]);
        $image = substr($imageData, strpos($imageData, ',') + 1);
        $decoded = base64_decode(str_replace(' ', '+', $image), true);

        $fileName = "attendance/$userId/" . Str::uuid() . ".$extension";

        Storage::disk('public')->put($fileName, $decoded);

        return $fileName;
    }

    private function rejectRequest(string $message): never
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => $message,
        ], 422));
    }

    private function validateTrustedLocation(array $validated, ?WorkSetting $setting): float|\Illuminate\Http\JsonResponse
    {
        $metadataResponse = $this->validateLocationMetadata($validated);
        if ($metadataResponse instanceof \Illuminate\Http\JsonResponse) {
            return $metadataResponse;
        }

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
                'status' => 'error',
                'message' => 'Anda berada di luar radius kantor (' . round($distance) . ' meter).'
            ], 403);
        }

        return $distance;
    }

    private function validateAttendanceNetwork(Request $request, ?WorkSetting $setting): ?\Illuminate\Http\JsonResponse
    {
        if (!($setting?->attendance_network_check_enabled ?? false)) {
            return null;
        }

        $clientIp = $request->ip();
        $allowedNetworks = IpNetwork::parseList($setting->attendance_allowed_networks);

        if (empty($allowedNetworks)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pembatasan jaringan kantor aktif, tetapi daftar IP kantor belum diatur. Hubungi admin.',
            ], 403);
        }

        if ($clientIp && IpNetwork::contains($allowedNetworks, $clientIp)) {
            return null;
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Absensi hanya dapat dilakukan dari jaringan kantor. IP perangkat Anda (' . ($clientIp ?: '-') . ') belum terdaftar sebagai IP/subnet kantor.',
        ], 403);
    }

    private function validateLocationMetadata(array $validated): ?\Illuminate\Http\JsonResponse
    {
        $maxLocationAccuracy = (float) config('attendance.web_max_location_accuracy_meters', 180);
        if ((float) $validated['location_accuracy'] > $maxLocationAccuracy) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akurasi lokasi terlalu rendah. Aktifkan GPS akurat lalu coba lagi.',
            ], 422);
        }

        $maxLocationAge = (float) config('attendance.max_location_age_seconds', 20);
        if ((float) $validated['location_age_seconds'] > $maxLocationAge) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data lokasi sudah terlalu lama. Ambil GPS ulang lalu coba lagi.',
            ], 422);
        }

        try {
            $locationTimestamp = Carbon::parse($validated['location_timestamp']);
        } catch (\Throwable) {
            return response()->json([
                'status' => 'error',
                'message' => 'Timestamp lokasi tidak valid.',
            ], 422);
        }

        $maxClientTimeSkew = (int) config('attendance.max_client_time_skew_seconds', 120);
        $timeSkew = abs(now()->diffInSeconds($locationTimestamp, false));
        if ($timeSkew > $maxClientTimeSkew) {
            return response()->json([
                'status' => 'error',
                'message' => 'Waktu perangkat tidak sinkron. Periksa jam perangkat lalu coba lagi.',
            ], 422);
        }

        return $this->validateLocationSamples(
            $validated['location_samples'] ?? [],
            (float) $validated['lat'],
            (float) $validated['lng']
        );
    }

    private function validateLocationSamples(array $samples, float $latitude, float $longitude): ?\Illuminate\Http\JsonResponse
    {
        $requiredSamples = (int) config('attendance.web_required_location_samples', 2);
        $fastLocationAccuracy = (float) config('attendance.fast_location_accuracy_meters', 25);
        $latestSample = count($samples) > 0 ? $samples[count($samples) - 1] : null;

        if (
            is_array($latestSample) &&
            isset($latestSample['accuracy']) &&
            (float) $latestSample['accuracy'] <= $fastLocationAccuracy
        ) {
            $requiredSamples = 1;
        }

        if (count($samples) < $requiredSamples) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sampel lokasi belum cukup. Tunggu GPS stabil lalu coba lagi.',
            ], 422);
        }

        $maxLocationAccuracy = (float) config('attendance.web_max_location_accuracy_meters', 180);
        $maxLocationAge = (float) config('attendance.max_location_age_seconds', 20);
        $maxClientTimeSkew = (int) config('attendance.max_client_time_skew_seconds', 120);
        $maxSampleSpread = (float) config('attendance.max_location_sample_spread_meters', 35);

        foreach ($samples as $sample) {
            if ((float) $sample['accuracy'] > $maxLocationAccuracy) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Salah satu sampel lokasi terlalu tidak akurat. Ambil GPS ulang lalu coba lagi.',
                ], 422);
            }

            if ((float) $sample['age_seconds'] > $maxLocationAge) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Salah satu sampel lokasi sudah kedaluwarsa. Ambil GPS ulang lalu coba lagi.',
                ], 422);
            }

            try {
                $sampleTimestamp = Carbon::parse($sample['timestamp']);
            } catch (\Throwable) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Timestamp sampel lokasi tidak valid.',
                ], 422);
            }

            $timeSkew = abs(now()->diffInSeconds($sampleTimestamp, false));
            if ($timeSkew > $maxClientTimeSkew) {
                return response()->json([
                    'status' => 'error',
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
                    'status' => 'error',
                    'message' => 'Pergerakan lokasi terlalu besar. Tunggu GPS stabil lalu coba lagi.',
                ], 422);
            }
        }

        return null;
    }

    private function passesQualityGate(array $qualityMetrics): bool
    {
        return $qualityMetrics['brightness'] >= (float) config('attendance.min_brightness', 30) &&
               $qualityMetrics['brightness'] <= (float) config('attendance.max_brightness', 220) &&
               $qualityMetrics['sharpness'] >= (float) config('attendance.min_sharpness', 8);
    }

    private function compareEmbeddings(array $storedEmbedding, array $incomingEmbedding): float
    {
        $sum = 0;
        foreach ($storedEmbedding as $i => $val) {
            $diff = $val - $incomingEmbedding[$i];
            $sum += $diff * $diff;
        }
        return sqrt($sum);
    }

    private function hasValidDescriptorSamples(?array $descriptorSamples): bool
    {
        if (!is_array($descriptorSamples) || count($descriptorSamples) < 3) {
            return false;
        }

        foreach ($descriptorSamples as $sample) {
            if (!is_array($sample) || count($sample) !== 128) {
                return false;
            }
        }

        return true;
    }

    private function findClosestDescriptorDistance(array $descriptorSamples, array $incomingEmbedding): float
    {
        $closestDistance = null;

        foreach ($descriptorSamples as $sample) {
            if (!is_array($sample) || count($sample) !== 128) {
                continue;
            }

            $distance = $this->compareEmbeddings($sample, $incomingEmbedding);

            if ($closestDistance === null || $distance < $closestDistance) {
                $closestDistance = $distance;
            }
        }

        return $closestDistance ?? INF;
    }

    private function findClosestOtherFaceDistance(int $userId, array $incomingEmbedding): ?float
    {
        $closestDistance = null;

        FaceEmbedding::query()
            ->where('user_id', '!=', $userId)
            ->select(['id', 'user_id', 'embedding'])
            ->chunkById(100, function ($faceEmbeddings) use ($incomingEmbedding, &$closestDistance) {
                foreach ($faceEmbeddings as $faceEmbedding) {
                    if (!is_array($faceEmbedding->embedding) || count($faceEmbedding->embedding) !== 128) {
                        continue;
                    }

                    $distance = $this->compareEmbeddings($faceEmbedding->embedding, $incomingEmbedding);

                    if ($closestDistance === null || $distance < $closestDistance) {
                        $closestDistance = $distance;
                    }
                }
            });

        return $closestDistance;
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
}
