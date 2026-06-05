<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\FaceEmbedding;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class FaceEnrollmentController extends Controller
{
    public function show()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasFaceEnrollment()) {
            return redirect()->route('face.verify.progress');
        }

        return view('user.face-enroll');
    }

    public function showVerificationProgress()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $verificationFailed = (bool) session('face_verification_failed', false);

        if (!$user->hasFaceEnrollment() && !$verificationFailed) {
            return redirect()->route('face.enroll');
        }

        return view('user.verification-progress', [
            'verificationFailed' => $verificationFailed,
            'verificationMessage' => session('face_verification_message', 'Wajah yang terlihat di setiap langkah berbeda. Ulangi pendaftaran dan pastikan orang yang sama mengikuti semua instruksi.'),
        ]);
    }

    public function showSuccess()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasFaceEnrollment()) {
            return redirect()->route('face.enroll');
        }

        return view('user.face-enroll-success');
    }

    public function status(): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return response()->json([
            'has_enrollment' => $user->hasFaceEnrollment(),
            'redirect' => $user->hasFaceEnrollment()
                ? route('face.verify.progress', [], false)
                : route('face.enroll', [], false),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'embedding' => ['required', 'array', 'size:128'],
            'embedding.*' => ['required', 'numeric'],
            'descriptor_samples' => ['required', 'array', 'min:3', 'max:5'],
            'descriptor_samples.*' => ['required', 'array', 'size:128'],
            'descriptor_samples.*.*' => ['required', 'numeric'],
            'quality_metrics' => ['required', 'array'],
            'quality_metrics.sample_count' => ['required', 'integer', 'min:3'],
            'quality_metrics.min_brightness' => ['required', 'numeric'],
            'quality_metrics.max_brightness' => ['required', 'numeric'],
            'quality_metrics.min_sharpness' => ['required', 'numeric'],
            'blink_verified' => ['required', 'accepted'],
            'image' => ['nullable', 'string'],
        ]);

        if (
            $validated['quality_metrics']['min_brightness'] < config('attendance.min_brightness') ||
            $validated['quality_metrics']['max_brightness'] > config('attendance.max_brightness') ||
            $validated['quality_metrics']['min_sharpness'] < config('attendance.min_sharpness')
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kualitas wajah tidak cukup baik. Ulangi pendaftaran di pencahayaan yang lebih jelas.',
            ], 422);
        }

        // ✅ FIXED: Jangan json_encode manual — model sudah cast 'array' otomatis
        $embedding = array_map('floatval', $validated['embedding']);
        $descriptorSamples = array_map(
            fn (array $sample) => array_map('floatval', $sample),
            $validated['descriptor_samples']
        );

        if (count($descriptorSamples) !== (int) $validated['quality_metrics']['sample_count']) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jumlah sampel wajah tidak sesuai. Ulangi pendaftaran wajah.',
            ], 422);
        }

        if (!$this->hasConsistentEnrollmentSamples($embedding, $descriptorSamples)) {
            session()->flash('face_verification_failed', true);
            session()->flash('face_verification_message', 'Wajah yang terlihat di setiap langkah berbeda. Ulangi pendaftaran dan pastikan orang yang sama mengikuti semua instruksi.');

            return response()->json([
                'status' => 'failed',
                'message' => 'Wajah tidak cocok. Mengalihkan ke halaman verifikasi.',
                'redirect' => route('face.verify.progress'),
            ]);
        }

        if ($this->isFaceRegisteredToAnotherUser(Auth::id(), $embedding, $descriptorSamples)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Wajah ini sudah terdaftar pada akun lain.',
            ], 422);
        }

        $existingFaceEmbedding = FaceEmbedding::query()
            ->where('user_id', Auth::id())
            ->first();
        $photoPath = !empty($validated['image'])
            ? $this->storeFaceImage($validated['image'], Auth::id())
            : $existingFaceEmbedding?->photo_path;

        FaceEmbedding::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'embedding' => $embedding,
                'descriptor_samples' => $descriptorSamples,
                'photo_path' => $photoPath,
            ]
        );

        if (
            $existingFaceEmbedding?->photo_path &&
            $photoPath &&
            $existingFaceEmbedding->photo_path !== $photoPath
        ) {
            Storage::disk('public')->delete($existingFaceEmbedding->photo_path);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data wajah berhasil disimpan.',
            'redirect' => route('face.verify.progress'),
        ]);
    }

    private function storeFaceImage(string $imageData, int $userId): string
    {
        if (!preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
            throw ValidationException::withMessages([
                'image' => 'Format foto wajah tidak valid.',
            ]);
        }

        $extension = strtolower($matches[1]);
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            throw ValidationException::withMessages([
                'image' => 'Format foto wajah harus JPG, PNG, atau WebP.',
            ]);
        }

        $image = substr($imageData, strpos($imageData, ',') + 1);
        $decoded = base64_decode(str_replace(' ', '+', $image), true);

        if ($decoded === false) {
            throw ValidationException::withMessages([
                'image' => 'Foto wajah gagal diproses.',
            ]);
        }

        $fileName = 'face-enrollment/user-' . $userId . '-' . now()->format('YmdHis') . '.jpg';
        Storage::disk('public')->put($fileName, $decoded);

        return $fileName;
    }

    private function hasConsistentEnrollmentSamples(array $embedding, array $descriptorSamples): bool
    {
        $averageThreshold = (float) config('attendance.enrollment_average_threshold', 0.38);
        $consistencyThreshold = (float) config('attendance.enrollment_consistency_threshold', 0.42);
        $referenceSample = $descriptorSamples[0] ?? null;

        if (!is_array($referenceSample) || count($referenceSample) !== 128) {
            return false;
        }

        foreach ($descriptorSamples as $sample) {
            if ($this->compareEmbeddings($embedding, $sample) > $averageThreshold) {
                return false;
            }

            if ($this->compareEmbeddings($referenceSample, $sample) > $consistencyThreshold) {
                return false;
            }
        }

        for ($i = 0; $i < count($descriptorSamples); $i++) {
            for ($j = $i + 1; $j < count($descriptorSamples); $j++) {
                if ($this->compareEmbeddings($descriptorSamples[$i], $descriptorSamples[$j]) > $consistencyThreshold) {
                    return false;
                }
            }
        }

        return true;
    }

    private function isFaceRegisteredToAnotherUser(int $userId, array $embedding, array $descriptorSamples): bool
    {
        $duplicateThreshold = (float) config('attendance.face_duplicate_threshold', 0.45);

        return FaceEmbedding::query()
            ->where('user_id', '!=', $userId)
            ->get(['user_id', 'embedding', 'descriptor_samples'])
            ->contains(function (FaceEmbedding $faceEmbedding) use ($embedding, $descriptorSamples, $duplicateThreshold) {
                if (!is_array($faceEmbedding->embedding) || count($faceEmbedding->embedding) !== 128) {
                    return false;
                }

                if ($this->compareEmbeddings($faceEmbedding->embedding, $embedding) <= $duplicateThreshold) {
                    return true;
                }

                foreach ($descriptorSamples as $sample) {
                    if ($this->compareEmbeddings($faceEmbedding->embedding, $sample) <= $duplicateThreshold) {
                        return true;
                    }
                }

                $storedSamples = $faceEmbedding->descriptor_samples;
                if (!is_array($storedSamples)) {
                    return false;
                }

                foreach ($storedSamples as $storedSample) {
                    if (
                        is_array($storedSample) &&
                        count($storedSample) === 128 &&
                        $this->compareEmbeddings($storedSample, $embedding) <= $duplicateThreshold
                    ) {
                        return true;
                    }
                }

                return false;
            });
    }

    private function compareEmbeddings(array $storedEmbedding, array $incomingEmbedding): float
    {
        $sum = 0;
        foreach ($storedEmbedding as $i => $val) {
            $diff = (float) $val - (float) $incomingEmbedding[$i];
            $sum += $diff * $diff;
        }

        return sqrt($sum);
    }
}
