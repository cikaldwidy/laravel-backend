<?php

namespace App\Http\Controllers;

use App\Models\FaceEmbedding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FaceEnrollmentController extends Controller
{
    public function show()
    {
        if (Auth::user()->hasFaceEnrollment()) {
            return redirect()->route('face.verify.progress');
        }

        return view('user.face-enroll');
    }

    public function showVerificationProgress()
    {
        if (!Auth::user()->hasFaceEnrollment()) {
            return redirect()->route('face.enroll');
        }

        return view('user.verification-progress');
    }

    public function showSuccess()
    {
        if (!Auth::user()->hasFaceEnrollment()) {
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

        FaceEmbedding::updateOrCreate(
            ['user_id' => Auth::id()],
            ['embedding' => json_encode($validated['embedding'])]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Data wajah berhasil disimpan.',
            'redirect' => route('face.verify.progress'),
        ]);
    }
}
