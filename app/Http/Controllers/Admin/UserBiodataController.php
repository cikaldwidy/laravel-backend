<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeeDetail;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserBiodataController extends Controller
{
    public function index()
    {
        $users = User::query()
            ->orderBy('name')
            ->get();

        $profiles = UserProfile::query()
            ->pluck('id', 'user_id');

        $details = EmployeeDetail::query()
            ->pluck('id', 'user_id');

        return view('admin.biodata.index', [
            'users' => $users,
            'profiles' => $profiles,
            'details' => $details,
        ]);
    }

    public function edit(User $user)
    {
        $profile = $user->userProfile;
        $employeeDetail = $user->employeeDetail;

        return view('admin.biodata.edit', compact('user', 'profile', 'employeeDetail'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'no_hp' => ['required', 'regex:/^[0-9]+$/', 'max:20'],
            'alamat' => ['required', 'string'],
            'tanggal_lahir' => ['required', 'date'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'nik' => ['nullable', 'regex:/^[0-9]+$/', 'max:32'],
            'nip' => ['required', 'string', 'max:50'],
            'departemen' => ['required', 'string', 'max:120'],
            'jabatan' => ['required', 'string', 'max:120'],
            'status_kerja' => ['required', 'in:tetap,kontrak,magang'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        $existingPhoto = $user->userProfile?->foto;
        $fotoPath = $existingPhoto;

        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('profiles', 'public');

            if ($existingPhoto && $existingPhoto !== $fotoPath) {
                Storage::disk('public')->delete($existingPhoto);
            }
        }

        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'no_hp' => $validated['no_hp'],
                'alamat' => $validated['alamat'],
                'tanggal_lahir' => $validated['tanggal_lahir'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'nik' => $validated['nik'] ?? null,
                'foto' => $fotoPath,
            ]
        );

        EmployeeDetail::updateOrCreate(
            ['user_id' => $user->id],
            [
                'nip' => $validated['nip'],
                'departemen' => $validated['departemen'],
                'jabatan' => $validated['jabatan'],
                'status_kerja' => $validated['status_kerja'],
            ]
        );

        return redirect()
            ->route('admin.biodata.index')
            ->with('success', 'Biodata user berhasil disimpan.');
    }

    public function destroy(User $user)
    {
        $profile = $user->userProfile;

        if ($profile?->foto) {
            Storage::disk('public')->delete($profile->foto);
        }

        UserProfile::query()->where('user_id', $user->id)->delete();
        EmployeeDetail::query()->where('user_id', $user->id)->delete();

        return redirect()
            ->route('admin.biodata.index')
            ->with('success', 'Biodata user berhasil dihapus.');
    }
}

