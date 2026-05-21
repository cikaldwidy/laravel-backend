<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserBiodataController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $profile = $user?->userProfile;
        $employeeDetail = $user?->employeeDetail?->loadMissing(['department', 'position']);

        return view('profile.profile', compact('user', 'profile', 'employeeDetail'));
    }

    public function edit()
    {
        return redirect()->route('profile.index', ['edit' => 1]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'no_hp' => ['required', 'regex:/^[0-9]+$/', 'max:20'],
            'alamat' => ['required', 'string'],
            'tanggal_lahir' => ['required', 'date'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'nik' => ['nullable', 'regex:/^[0-9]+$/', 'max:32'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], [], [
            'no_hp' => 'no. HP',
            'tanggal_lahir' => 'tanggal lahir',
            'jenis_kelamin' => 'jenis kelamin',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            if ($user->userProfile?->foto) {
                Storage::disk('public')->delete($user->userProfile->foto);
            }

            $fotoPath = $request->file('foto')->store('profiles', 'public');
        }

        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'no_hp' => $validated['no_hp'],
                'alamat' => $validated['alamat'],
                'tanggal_lahir' => $validated['tanggal_lahir'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'nik' => $validated['nik'] ?? null,
                'foto' => $fotoPath ?? ($user->userProfile?->foto),
            ]
        );

        return redirect('/profile')->with('success', 'Biodata berhasil disimpan.');
    }
}
