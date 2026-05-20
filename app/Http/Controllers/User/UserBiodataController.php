<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\EmployeeDetail;
use App\Models\Position;
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
        if (Auth::user()?->role !== 'admin') {
            abort(403);
        }

        $user = Auth::user();

        $profile = $user?->userProfile;
        $employeeDetail = $user?->employeeDetail?->loadMissing(['department', 'position']);
        $departments = Department::query()
            ->with([
                'positions' => fn ($query) => $query->orderBy('nama_jabatan'),
            ])
            ->orderBy('nama_departemen')
            ->get();

        return view('profile.edit', compact('user', 'profile', 'employeeDetail', 'departments'));
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
            'department_id' => ['required', 'exists:departments,id'],
            'position_id' => ['required', 'exists:positions,id'],
            'nip' => ['required', 'string', 'max:50'],
            'status_kerja' => ['required', 'in:tetap,kontrak,magang'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], [], [
            'department_id' => 'unit kerja/bagian',
            'position_id' => 'jabatan',
            'status_kerja' => 'status kerja',
            'no_hp' => 'no. HP',
            'tanggal_lahir' => 'tanggal lahir',
            'jenis_kelamin' => 'jenis kelamin',
        ]);

        $department = Department::query()->findOrFail($validated['department_id']);
        $position = Position::query()->findOrFail($validated['position_id']);

        if ((int) $position->department_id !== (int) $department->id) {
            return back()->withErrors(['position_id' => 'Jabatan yang dipilih tidak sesuai dengan unit kerja/bagian.'])->withInput();
        }

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
                'department_id' => $department->id,
                'unit_id' => null,
                'position_id' => $position->id,
                'nip' => $validated['nip'],
                'departemen' => $department->nama_departemen,
                'jabatan' => $position->nama_jabatan,
                'status_kerja' => $validated['status_kerja'],
            ]
        );

        return redirect('/profile')->with('success', 'Biodata berhasil disimpan.');
    }
}
