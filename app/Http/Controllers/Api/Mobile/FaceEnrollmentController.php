<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\FaceEmbedding;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FaceEnrollmentController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'images' => ['required', 'array', 'min:3', 'max:3'],
            'images.*' => ['required', 'string'],
            'face_detected' => ['required', 'accepted'],
            'blink_verified' => ['required', 'accepted'],
        ]);

        /** @var User $user */
        $user = $request->user();

        if ($user->role !== 'user') {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint ini hanya untuk user.',
            ], 403);
        }

        $photoPath = null;
        foreach ($validated['images'] as $image) {
            $photoPath = $this->storeFaceImage($image, $user->id);
        }

        FaceEmbedding::updateOrCreate(
            ['user_id' => $user->id],
            [
                'embedding' => $this->buildMobileEmbedding($validated['images']),
                'photo_path' => $photoPath,
            ],
        );

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran wajah berhasil disimpan.',
            'data' => [
                'has_face_enrollment' => true,
                'photo_path' => $photoPath,
            ],
        ]);
    }

    private function buildMobileEmbedding(array $images): array
    {
        $seed = implode('|', array_map(
            fn (string $image) => hash('sha256', substr($image, 0, 512) . strlen($image)),
            $images,
        ));

        $hash = hash('sha512', $seed);
        $values = [];

        for ($index = 0; $index < 128; $index++) {
            $chunk = substr($hash, ($index * 2) % strlen($hash), 2);
            $values[] = round(hexdec($chunk) / 255, 6);
        }

        return $values;
    }

    private function storeFaceImage(string $imageData, int $userId): string
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

        $fileName = "face-enrollment/$userId/" . Str::uuid() . ".$extension";
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
}
