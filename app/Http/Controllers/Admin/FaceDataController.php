<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FaceEmbedding;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FaceDataController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
        ]);

        $selectedUnitId = $validated['unit_id'] ?? null;
        $units = Unit::query()
            ->orderBy('nama_unit')
            ->get();
        $selectedUnit = $selectedUnitId ? $units->firstWhere('id', (int) $selectedUnitId) : null;

        $faceEmbeddings = FaceEmbedding::query()
            ->with('user.employeeDetail.unit')
            ->when($selectedUnitId, function ($query) use ($selectedUnitId) {
                $query->whereHas('user.employeeDetail', function ($detailQuery) use ($selectedUnitId) {
                    $detailQuery->where('unit_id', $selectedUnitId);
                });
            })
            ->latest()
            ->get();

        $baseUserQuery = User::query()
            ->where('role', 'user')
            ->with('employeeDetail.unit')
            ->when($selectedUnitId, function ($query) use ($selectedUnitId) {
                $query->whereHas('employeeDetail', function ($detailQuery) use ($selectedUnitId) {
                    $detailQuery->where('unit_id', $selectedUnitId);
                });
            });

        $usersWithoutFaceData = (clone $baseUserQuery)
            ->whereDoesntHave('faceEmbedding')
            ->orderBy('name')
            ->get();

        $usersWithFaceData = (clone $baseUserQuery)
            ->whereHas('faceEmbedding')
            ->orderBy('name')
            ->get();

        return view('admin.face_data.index', compact(
            'faceEmbeddings',
            'usersWithoutFaceData',
            'usersWithFaceData',
            'units',
            'selectedUnitId',
            'selectedUnit'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id', Rule::unique('face_embeddings', 'user_id')],
            'embedding' => ['required', 'array', 'size:128'],
            'embedding.*' => ['required', 'numeric'],
            'image' => ['required', 'string'],
            'blink_verified' => ['required', 'accepted'],
        ]);

        FaceEmbedding::create([
            'user_id' => $validated['user_id'],
            'embedding' => array_map('floatval', $validated['embedding']),
            'photo_path' => $this->storeFaceImage($validated['image'], (int) $validated['user_id']),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data wajah berhasil ditambahkan.',
            'redirect' => route('admin.face_data.index'),
        ]);
    }

    public function update(Request $request, FaceEmbedding $faceEmbedding)
    {
        $validated = $request->validate([
            'embedding' => ['required', 'array', 'size:128'],
            'embedding.*' => ['required', 'numeric'],
            'image' => ['required', 'string'],
            'blink_verified' => ['required', 'accepted'],
        ]);

        $oldPhotoPath = $faceEmbedding->photo_path;

        $faceEmbedding->update([
            'embedding' => array_map('floatval', $validated['embedding']),
            'photo_path' => $this->storeFaceImage($validated['image'], $faceEmbedding->user_id),
        ]);

        if ($oldPhotoPath && $oldPhotoPath !== $faceEmbedding->photo_path) {
            Storage::disk('public')->delete($oldPhotoPath);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data wajah berhasil diperbarui.',
            'redirect' => route('admin.face_data.index'),
        ]);
    }

    public function destroy(FaceEmbedding $faceEmbedding)
    {
        if ($faceEmbedding->photo_path) {
            Storage::disk('public')->delete($faceEmbedding->photo_path);
        }

        $faceEmbedding->delete();

        return back()->with('success', 'Data wajah berhasil dihapus.');
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

        $fileName = 'face-data/user-' . $userId . '-' . now()->format('YmdHis') . '.jpg';
        Storage::disk('public')->put($fileName, $decoded);

        return $fileName;
    }
}
