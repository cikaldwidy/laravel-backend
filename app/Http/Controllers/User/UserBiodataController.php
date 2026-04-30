<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\EmployeeDetail;
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
        $employeeDetail = $user?->employeeDetail;

        return view('profile.profile', compact('user', 'profile', 'employeeDetail'));
    }

    public function edit()
    {
        if (Auth::user()?->role !== 'admin') {
            abort(403);
        }

        $user = Auth::user();

        $profile = $user?->userProfile;
        $employeeDetail = $user?->employeeDetail;

        return view('profile.edit', compact('user', 'profile', 'employeeDetail'));
    }

    public function update(Request $request)
    {
        if (Auth::user()?->role !== 'admin') {
            abort(403);
        }

        $user = Auth::user();

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

        $fotoPath = null;
        if ($request->hasFile('foto')) {
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

        EmployeeDetail::updateOrCreate(
            ['user_id' => $user->id],
            [
                'nip' => $validated['nip'],
                'departemen' => $validated['departemen'],
                'jabatan' => $validated['jabatan'],
                'status_kerja' => $validated['status_kerja'],
            ]
        );

        return redirect('/profile')->with('success', 'Biodata berhasil disimpan.');
    }
}
