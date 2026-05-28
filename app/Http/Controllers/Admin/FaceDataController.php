<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\FaceEmbedding;
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
            'unit_id' => ['nullable', 'integer', 'exists:departments,id'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $selectedUnitId = $validated['unit_id'] ?? null;
        $search = trim($validated['search'] ?? '') ?: null;
        $units = Department::query()
            ->orderBy('nama_departemen')
            ->get();
        $selectedUnit = $selectedUnitId ? $units->firstWhere('id', (int) $selectedUnitId) : null;

        $faceEmbeddings = FaceEmbedding::query()
            ->with('user.employeeDetail.department')
            ->when($selectedUnitId, function ($query) use ($selectedUnitId) {
                $query->whereHas('user.employeeDetail', function ($detailQuery) use ($selectedUnitId) {
                    $detailQuery->where('department_id', $selectedUnitId);
                });
            })
            ->when($search, function ($query) use ($search) {
                $query->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('username', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhereHas('employeeDetail', function ($detailQuery) use ($search) {
                            $detailQuery->where('nip', 'like', '%' . $search . '%')
                                ->orWhere('departemen', 'like', '%' . $search . '%')
                                ->orWhere('jabatan', 'like', '%' . $search . '%')
                                ->orWhereHas('department', fn ($department) => $department->where('nama_departemen', 'like', '%' . $search . '%'))
                                ->orWhereHas('position', fn ($position) => $position->where('nama_jabatan', 'like', '%' . $search . '%'));
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $baseUserQuery = User::query()
            ->where('role', 'user')
            ->with('employeeDetail.department')
            ->when($selectedUnitId, function ($query) use ($selectedUnitId) {
                $query->whereHas('employeeDetail', function ($detailQuery) use ($selectedUnitId) {
                    $detailQuery->where('department_id', $selectedUnitId);
                });
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('username', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhereHas('employeeDetail', function ($detailQuery) use ($search) {
                            $detailQuery->where('nip', 'like', '%' . $search . '%')
                                ->orWhere('departemen', 'like', '%' . $search . '%')
                                ->orWhere('jabatan', 'like', '%' . $search . '%')
                                ->orWhereHas('department', fn ($department) => $department->where('nama_departemen', 'like', '%' . $search . '%'))
                                ->orWhereHas('position', fn ($position) => $position->where('nama_jabatan', 'like', '%' . $search . '%'));
                        });
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

        $embedding = array_map('floatval', $validated['embedding']);

        if ($this->isFaceRegisteredToAnotherUser((int) $validated['user_id'], $embedding)) {
            throw ValidationException::withMessages([
                'embedding' => 'Wajah ini sudah terdaftar pada akun lain.',
            ]);
        }

        FaceEmbedding::create([
            'user_id' => $validated['user_id'],
            'embedding' => $embedding,
            'descriptor_samples' => [$embedding, $embedding, $embedding],
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

        $embedding = array_map('floatval', $validated['embedding']);

        if ($this->isFaceRegisteredToAnotherUser($faceEmbedding->user_id, $embedding)) {
            throw ValidationException::withMessages([
                'embedding' => 'Wajah ini sudah terdaftar pada akun lain.',
            ]);
        }

        $oldPhotoPath = $faceEmbedding->photo_path;

        $faceEmbedding->update([
            'embedding' => $embedding,
            'descriptor_samples' => [$embedding, $embedding, $embedding],
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

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:face_embeddings,id'],
        ]);

        $faceEmbeddings = FaceEmbedding::query()
            ->whereIn('id', $validated['ids'])
            ->get();

        foreach ($faceEmbeddings as $faceEmbedding) {
            if ($faceEmbedding->photo_path) {
                Storage::disk('public')->delete($faceEmbedding->photo_path);
            }

            $faceEmbedding->delete();
        }

        if ($faceEmbeddings->isEmpty()) {
            return back()->with('error', 'Tidak ada data wajah yang bisa dihapus.');
        }

        return back()->with('success', $faceEmbeddings->count() . ' data wajah berhasil dihapus.');
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

    private function isFaceRegisteredToAnotherUser(int $userId, array $embedding): bool
    {
        $duplicateThreshold = (float) config('attendance.face_duplicate_threshold', 0.45);

        return FaceEmbedding::query()
            ->where('user_id', '!=', $userId)
            ->get(['user_id', 'embedding', 'descriptor_samples'])
            ->contains(function (FaceEmbedding $faceEmbedding) use ($embedding, $duplicateThreshold) {
                if (!is_array($faceEmbedding->embedding) || count($faceEmbedding->embedding) !== 128) {
                    return false;
                }

                if ($this->compareEmbeddings($faceEmbedding->embedding, $embedding) <= $duplicateThreshold) {
                    return true;
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
