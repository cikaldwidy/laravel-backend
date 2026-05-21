<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load([
            'userProfile',
            'employeeDetail.department',
            'employeeDetail.unit',
            'employeeDetail.position',
            'faceEmbedding',
        ]);

        $profile = $user->userProfile;
        $employee = $user->employeeDetail;

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diambil',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'no_hp' => $profile?->no_hp,
                'alamat' => $profile?->alamat,
                'tanggal_lahir' => $profile?->tanggal_lahir?->toDateString(),
                'jenis_kelamin' => $profile?->jenis_kelamin,
                'agama' => $profile?->agama,
                'nik' => $profile?->nik,
                'foto' => $profile?->foto ? asset('storage/' . $profile->foto) : null,
                'nip' => $employee?->nip,
                'status_kerja' => $employee?->status_kerja,
                'department' => $employee?->department?->nama_departemen,
                'unit' => $employee?->unit?->nama_unit,
                'position' => $employee?->position?->nama_jabatan,
                'has_face_enrollment' => $user->faceEmbedding !== null,
            ],
        ]);
    }
}
