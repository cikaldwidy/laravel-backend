<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\User;
use App\Models\UserShift;
use App\Models\WorkSetting;
use App\Support\ShiftTime;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
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
        $activeShiftContext = $this->resolveActiveShift($user, $now);
        $scheduledShift = $this->getTodayShiftAssignment($user, $now)?->shift;
        $activeShift = $activeShiftContext['shift'] ?? null;
        $canAttend = (bool) $activeShiftContext;
        $tanggalPresensi = $activeShiftContext['shift_date'] ?? $now->copy()->startOfDay();

        $presensi = Presensi::where('user_id', $user->id)
            ->whereDate('tanggal', $tanggalPresensi)
            ->first();
        $setting = WorkSetting::first();

        return view('user.absen', [
            'presensi' => $presensi,
            'workSetting' => $setting,
            'activeShift' => $activeShift,
            'scheduledShift' => $scheduledShift,
            'canAttend' => $canAttend,
            'faceThreshold' => config('attendance.face_threshold', 0.55),
            'officeRadius' => $setting->radius_meters ?? config('attendance.radius_meters', 100),
            'officeLatitude' => $setting->office_latitude ?? config('attendance.office_latitude'),
            'officeLongitude' => $setting->office_longitude ?? config('attendance.office_longitude'),
        ]);
    }

    public function challenge(Request $request)
    {
        $steps = collect(['center', 'left', 'right'])
            ->shuffle()
            ->values()
            ->all();

        $challenge = [
            'token' => (string) Str::uuid(),
            'steps' => $steps,
            'issued_at' => now()->timestamp,
        ];

        return response()->json([
            'token' => $challenge['token'],
            'steps' => $challenge['steps'],
            'issued_at' => $challenge['issued_at'],
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
          ->header('Pragma', 'no-cache')
          ->header('Expires', '0');
    }

    public function absen(Request $request)
    {
        $validated = $request->validate([
            'image' => ['required', 'string'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'embedding' => ['required', 'array', 'size:128'],
            'embedding.*' => ['required', 'numeric'],
            'quality_metrics' => ['required', 'array'],
            'quality_metrics.brightness' => ['required', 'numeric'],
            'quality_metrics.sharpness' => ['required', 'numeric'],
            'challenge_token' => ['required', 'string'],
            'challenge_steps' => ['required', 'array', 'size:3'],
            'challenge_steps.*' => ['required', 'in:center,left,right'],
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

        if (!$this->isValidChallenge(
            $validated['challenge_token'],
            $validated['challenge_steps']
        )) {
            return response()->json([
                'status' => 'error',
                'message' => 'Challenge liveness tidak valid atau sudah kedaluwarsa.',
                'debug' => [
                    'server_time' => now()->timestamp,
                    'steps' => array_values($validated['challenge_steps']),
                ],
            ], 422);
        }

        if (!$this->passesQualityGate($validated['quality_metrics'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Wajah tidak cukup jelas. Ulangi scan di tempat yang lebih terang dan stabil.',
            ], 422);
        }

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

        $storedEmbedding = $user->faceEmbedding?->embedding;

        if (!is_array($storedEmbedding) || count($storedEmbedding) !== 128) {
            return response()->json([
                'status' => 'error',
                'message' => 'Template wajah user tidak valid. Silakan daftar ulang wajah.',
            ], 422);
        }

        $faceDistance = $this->compareEmbeddings($storedEmbedding, $validated['embedding']);

        if ($faceDistance > config('attendance.face_threshold')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Verifikasi wajah gagal.',
                'face_distance' => round($faceDistance, 6),
            ], 422);
        }

        $now = now();
        $activeShiftContext = $this->resolveActiveShift($user, $now);

        if (!$activeShiftContext) {
            return response()->json([
                'status' => 'error',
                'message' => 'Shift belum diatur untuk Anda atau Anda berada di luar jam shift.',
            ], 403);
        }

        // Tanggal presensi mengikuti tanggal shift agar shift malam setelah tengah malam tetap dianggap shift kemarin.
        $tanggalPresensi = $activeShiftContext['shift_date']->toDateString();
        $jamMasukShift = $activeShiftContext['start'];
        $jamPulangShift = $activeShiftContext['end'];

        $presensi = Presensi::where('user_id', $user->id)
            ->whereDate('tanggal', $tanggalPresensi)
            ->first();

        $photoPath = $this->storeAttendanceImage($validated['image'], $user->id);
        $challengePayload = [
            'token' => $validated['challenge_token'],
            'steps' => $validated['challenge_steps'],
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
                'liveness_challenge' => $challengePayload,
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
            'liveness_challenge' => $challengePayload,
            'status_pulang' => 'normal',
        ]);

        return response()->json([
            'status' => 'pulang',
            'message' => 'Absen pulang berhasil',
            'status_pulang' => 'normal',
            'redirect' => route('dashboard', [], false),
        ]);
    }

    private function getTodayShiftAssignment(User $user, Carbon $now): ?UserShift
    {
        return UserShift::query()
            ->with('shift')
            ->where('user_id', $user->id)
            ->whereDate('tanggal', $now->toDateString())
            ->first();
    }

    private function resolveActiveShift(User $user, Carbon $now): ?array
    {
        // Cek shift hari ini dan shift kemarin untuk handle shift lintas hari.
        $shiftCandidates = UserShift::query()
            ->with('shift')
            ->where('user_id', $user->id)
            ->whereIn('tanggal', [
                $now->toDateString(),
                $now->copy()->subDay()->toDateString(),
            ])
            ->orderByDesc('tanggal')
            ->get();

        foreach ($shiftCandidates as $candidate) {
            if (!$candidate->shift) {
                continue;
            }

            $shiftDate = Carbon::parse($candidate->tanggal)->startOfDay();
            $window = ShiftTime::window($shiftDate, $candidate->shift->jam_masuk, $candidate->shift->jam_pulang, 60, 180);

            if (!$now->between($window['allowed_start'], $window['allowed_end'], true)) {
                continue;
            }

            return [
                'assignment' => $candidate,
                'shift' => $candidate->shift,
                'shift_date' => $shiftDate,
                'start' => $window['start'],
                'end' => $window['end'],
                'is_overnight' => ShiftTime::isOvernight($candidate->shift->jam_masuk, $candidate->shift->jam_pulang),
            ];
        }

        return null;
    }

    // ================= HELPER =================

    private function isValidChallenge(string $token, array $steps): bool
    {
        if (empty($token)) {
            return false;
        }

        $normalizedSteps = array_values($steps);
        $expectedSteps = ['center', 'left', 'right'];
        sort($normalizedSteps);
        sort($expectedSteps);

        return $normalizedSteps === $expectedSteps;
    }

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

    private function passesQualityGate(array $qualityMetrics): bool
    {
        return $qualityMetrics['brightness'] >= 55 &&
               $qualityMetrics['sharpness'] >= 18;
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
