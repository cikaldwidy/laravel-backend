<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PresensiController extends Controller
{
    public function show()
    {
        if (!Auth::user()->hasFaceEnrollment()) {
            return redirect()->route('face.enroll');
        }

        $presensi = Presensi::where('user_id', Auth::id())
            ->whereDate('tanggal', today())
            ->first();

        return view('user.absen', [
            'presensi' => $presensi,
            'faceThreshold' => config('attendance.face_threshold', 0.55),
            'officeRadius' => config('attendance.radius_meters', 100),
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

        $user = Auth::user();

        if (!$user->hasFaceEnrollment()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Wajah belum terdaftar. Selesaikan enrollment terlebih dulu.',
            ], 422);
        }

        $officeLatitude = (float) config('attendance.office_latitude', -6.123456);
        $officeLongitude = (float) config('attendance.office_longitude', 106.123456);
        $officeRadius = (int) config('attendance.radius_meters', 100);

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
                'message' => 'Verifikasi wajah gagal. Pastikan wajah yang dipindai sesuai dengan data terdaftar.',
                'face_distance' => round($faceDistance, 6),
            ], 422);
        }

        $today = today()->toDateString();
        $presensi = Presensi::where('user_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        $photoPath = $this->storeAttendanceImage($validated['image'], $user->id);
        $challengePayload = [
            'token' => $validated['challenge_token'],
            'steps' => $validated['challenge_steps'],
            'verified_at' => now()->toIso8601String(),
        ];

        if (!$presensi) {
            Presensi::create([
                'user_id' => $user->id,
                'tanggal' => $today,
                'jam_masuk' => now(),
                'foto' => $photoPath,
                'foto_masuk' => $photoPath,
                'latitude_masuk' => $validated['lat'],
                'longitude_masuk' => $validated['lng'],
                'jarak_masuk' => round($distance, 2),
                'face_distance_masuk' => round($faceDistance, 6),
                'liveness_challenge' => $challengePayload,
            ]);
            return response()->json([
                'status' => 'masuk',
                'message' => 'Absen masuk berhasil diverifikasi.',
                'redirect' => route('dashboard'),
            ]);
        }

        if (!$presensi->jam_keluar) {
            $presensi->update([
                'jam_keluar' => now(),
                'foto_keluar' => $photoPath,
                'latitude_keluar' => $validated['lat'],
                'longitude_keluar' => $validated['lng'],
                'jarak_keluar' => round($distance, 2),
                'face_distance_keluar' => round($faceDistance, 6),
                'liveness_challenge' => $challengePayload,
            ]);
            return response()->json([
                'status' => 'pulang',
                'message' => 'Absen pulang berhasil diverifikasi.',
                'redirect' => route('dashboard'),
            ]);
        }

        return response()->json([
            'status' => 'done',
            'message' => 'Anda sudah melakukan absen masuk dan pulang hari ini'
        ], 409);
    }

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
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($extension, $allowedExtensions, true)) {
            $this->rejectRequest('Tipe gambar tidak didukung.');
        }

        $image = substr($imageData, strpos($imageData, ',') + 1);
        $decoded = base64_decode(str_replace(' ', '+', $image), true);

        if ($decoded === false) {
            $this->rejectRequest('Gambar tidak dapat diproses.');
        }

        $fileName = sprintf(
            'attendance/%s/%s.%s',
            $userId,
            Str::uuid(),
            $extension === 'jpeg' ? 'jpg' : $extension
        );

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
        $brightness = (float) ($qualityMetrics['brightness'] ?? 0);
        $sharpness = (float) ($qualityMetrics['sharpness'] ?? 0);

        return $brightness >= config('attendance.min_brightness', 55) &&
            $brightness <= config('attendance.max_brightness', 210) &&
            $sharpness >= config('attendance.min_sharpness', 18);
    }

    private function compareEmbeddings(array $storedEmbedding, array $incomingEmbedding): float
    {
        $sum = 0.0;

        foreach ($storedEmbedding as $index => $value) {
            $difference = ((float) $value) - ((float) $incomingEmbedding[$index]);
            $sum += $difference * $difference;
        }

        return sqrt($sum);
    }

    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000;
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
