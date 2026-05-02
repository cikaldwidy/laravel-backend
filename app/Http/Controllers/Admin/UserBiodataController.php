<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\EmployeeDetail;
use App\Models\Position;
use App\Models\Unit;
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
        $user->load(['userProfile', 'employeeDetail.department', 'employeeDetail.unit', 'employeeDetail.position']);
        $profile = $user->userProfile;
        $employeeDetail = $user->employeeDetail;
        $departments = Department::query()
            ->with([
                'units' => fn ($query) => $query->orderBy('nama_unit'),
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
            'nik' => ['nullable', 'regex:/^[0-9]+$/', 'max:32'],
            'department_id' => ['required', 'exists:departments,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'position_id' => ['required', 'exists:positions,id'],
            'nip' => ['required', 'string', 'max:50'],
            'status_kerja' => ['required', 'in:tetap,kontrak,magang'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        $department = Department::query()->findOrFail($validated['department_id']);
        $unit = Unit::query()->findOrFail($validated['unit_id']);
        $position = Position::query()->findOrFail($validated['position_id']);

        if ((int) $unit->department_id !== (int) $department->id) {
            return back()->withErrors(['unit_id' => 'Unit yang dipilih tidak sesuai dengan departemen.'])->withInput();
        }

        if ((int) $position->department_id !== (int) $department->id) {
            return back()->withErrors(['position_id' => 'Jabatan yang dipilih tidak sesuai dengan departemen.'])->withInput();
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
                'nik' => $validated['nik'] ?? null,
                'foto' => $fotoPath,
            ]
        );

        EmployeeDetail::updateOrCreate(
            ['user_id' => $user->id],
            [
                'department_id' => $department->id,
                'unit_id' => $unit->id,
                'position_id' => $position->id,
                'nip' => $validated['nip'],
                'departemen' => $department->nama_departemen,
                'jabatan' => $position->nama_jabatan,
                'status_kerja' => $validated['status_kerja'],
            ]
        );

        return redirect()
            ->route('admin.users.index')
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
            ->route('admin.users.index')
            ->with('success', 'Biodata user berhasil dihapus.');
    }
}
