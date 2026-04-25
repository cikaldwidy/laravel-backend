<?php

namespace App\Http\Controllers;

use App\Models\FaceEmbedding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        if (!$user->hasFaceEnrollment()) {
            return redirect()->route('face.enroll');
        }

        return view('user.verification-progress');
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'embedding' => ['required', 'array', 'size:128'],
            'embedding.*' => ['required', 'numeric'],
            'quality_metrics' => ['required', 'array'],
            'quality_metrics.sample_count' => ['required', 'integer', 'min:3'],
            'quality_metrics.min_brightness' => ['required', 'numeric'],
            'quality_metrics.max_brightness' => ['required', 'numeric'],
            'quality_metrics.min_sharpness' => ['required', 'numeric'],
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
        FaceEmbedding::updateOrCreate(
            ['user_id' => Auth::id()],
            ['embedding' => $validated['embedding']]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Data wajah berhasil disimpan.',
            'redirect' => route('face.verify.progress'),
        ]);
    }
}