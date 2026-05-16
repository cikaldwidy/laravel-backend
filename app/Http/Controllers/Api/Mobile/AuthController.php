<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\EmployeeDetail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $login = trim($validated['login']);
        $normalizedLogin = strtolower($login);

        $user = User::query()
            ->with(['userProfile', 'employeeDetail.department', 'employeeDetail.unit', 'employeeDetail.position', 'faceEmbedding'])
            ->where('role', 'user')
            ->where(function ($query) use ($normalizedLogin) {
                $query->where('username', $normalizedLogin)
                    ->orWhere('email', $normalizedLogin);
            })
            ->first();

        if (!$user) {
            $employee = EmployeeDetail::query()
                ->with([
                    'user.userProfile',
                    'user.employeeDetail.department',
                    'user.employeeDetail.unit',
                    'user.employeeDetail.position',
                    'user.faceEmbedding',
                ])
                ->where('nip', $login)
                ->first();

            $user = $employee?->user;
        }

        if (!$user || $user->role !== 'user' || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Login gagal. Periksa username, email, NIP, atau password.',
            ], 422);
        }

        $token = $user->createToken($validated['device_name'] ?? 'flutter-mobile')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => $this->formatUser($user),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil',
        ]);
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $request->user()->load([
            'userProfile',
            'employeeDetail.department',
            'employeeDetail.unit',
            'employeeDetail.position',
            'faceEmbedding',
        ]);

        if ($user->role !== 'user') {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint ini hanya untuk user.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diambil',
            'data' => $this->formatUser($user),
        ]);
    }

    private function formatUser(User $user): array
    {
        $profile = $user->userProfile;
        $employee = $user->employeeDetail;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
            'no_hp' => $profile?->no_hp,
            'alamat' => $profile?->alamat,
            'tanggal_lahir' => $profile?->tanggal_lahir?->toDateString(),
            'jenis_kelamin' => $profile?->jenis_kelamin,
            'nik' => $profile?->nik,
            'foto' => $profile?->foto ? asset('storage/' . $profile->foto) : null,
            'nip' => $employee?->nip,
            'status_kerja' => $employee?->status_kerja,
            'department' => $employee?->department?->nama_departemen,
            'unit' => $employee?->unit?->nama_unit,
            'position' => $employee?->position?->nama_jabatan,
            'has_face_enrollment' => $user->faceEmbedding !== null,
        ];
    }
}
