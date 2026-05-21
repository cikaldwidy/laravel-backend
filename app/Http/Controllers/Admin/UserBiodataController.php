<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\EmployeeDetail;
use App\Models\Position;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserBiodataController extends Controller
{
    public function index()
    {
        return redirect()->route('admin.users.index');
    }

    public function edit(User $user)
    {
        $user->load(['userProfile', 'employeeDetail.department', 'employeeDetail.position']);
        $profile = $user->userProfile;
        $employeeDetail = $user->employeeDetail;
        $departments = Department::query()
            ->with([
                'positions' => fn ($query) => $query->orderBy('nama_jabatan'),
            ])
            ->orderBy('nama_departemen')
            ->get();

        return view('admin.biodata.edit', compact('user', 'profile', 'employeeDetail', 'departments'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'no_hp' => ['required', 'regex:/^[0-9]+$/', 'max:20'],
            'alamat' => ['required', 'string'],
            'tanggal_lahir' => ['required', 'date'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'agama' => ['required', 'string', 'max:50'],
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
                'agama' => $validated['agama'],
                'nik' => $validated['nik'] ?? null,
                'foto' => $fotoPath,
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

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Biodata pegawai berhasil disimpan.');
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
            ->route('admin.users.index')
            ->with('success', 'Biodata pegawai berhasil dihapus.');
    }
}
