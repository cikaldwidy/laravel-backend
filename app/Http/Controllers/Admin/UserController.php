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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // ================= LIST =================
    public function index(Request $request)
    {
        $users = User::query()
            ->with(['employeeDetail.department', 'employeeDetail.unit', 'employeeDetail.position', 'userProfile', 'faceEmbedding'])
            ->where('role', 'user')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhereHas('employeeDetail', function ($detail) use ($search) {
                            $detail->where('nip', 'like', '%' . $search . '%')
                                ->orWhere('departemen', 'like', '%' . $search . '%')
                                ->orWhere('jabatan', 'like', '%' . $search . '%')
                                ->orWhereHas('unit', fn ($unit) => $unit->where('nama_unit', 'like', '%' . $search . '%'))
                                ->orWhereHas('department', fn ($department) => $department->where('nama_departemen', 'like', '%' . $search . '%'))
                                ->orWhereHas('position', fn ($position) => $position->where('nama_jabatan', 'like', '%' . $search . '%'));
                        })
                        ->orWhereHas('userProfile', function ($profile) use ($search) {
                            $profile->where('nik', 'like', '%' . $search . '%')
                                ->orWhere('no_hp', 'like', '%' . $search . '%');
                        });
                });
            })
            ->when($request->filled('unit'), function ($query) use ($request) {
                $query->whereHas('employeeDetail', function ($detail) use ($request) {
                    $detail->whereHas('unit', fn ($unit) => $unit->where('nama_unit', $request->unit))
                        ->orWhereHas('department', fn ($department) => $department->where('nama_departemen', $request->unit));
                });
            })
            ->when($request->filled('biodata'), function ($query) use ($request) {
                match ($request->biodata) {
                    'lengkap' => $query->whereHas('userProfile')->whereHas('employeeDetail'),
                    'sebagian' => $query->where(function ($q) {
                        $q->where(function ($sub) {
                            $sub->whereHas('userProfile')->whereDoesntHave('employeeDetail');
                        })->orWhere(function ($sub) {
                            $sub->whereDoesntHave('userProfile')->whereHas('employeeDetail');
                        });
                    }),
                    'belum' => $query->whereDoesntHave('userProfile')->whereDoesntHave('employeeDetail'),
                    default => null,
                };
            })
            ->when($request->filled('wajah'), function ($query) use ($request) {
                $request->wajah === 'terdaftar'
                    ? $query->whereHas('faceEmbedding')
                    : $query->whereDoesntHave('faceEmbedding');
            })
            ->latest()
            ->get();

        $units = User::query()
            ->with(['employeeDetail.department', 'employeeDetail.unit'])
            ->get()
            ->map(fn ($user) => $user->employeeDetail?->unit?->nama_unit ?? $user->employeeDetail?->department?->nama_departemen)
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view('admin.users.index', compact('users', 'units'));
    }

    // ================= CREATE =================
    public function create()
    {
        $departments = Department::query()
            ->with([
                'units' => fn ($query) => $query->orderBy('nama_unit'),
                'positions' => fn ($query) => $query->orderBy('nama_jabatan'),
            ])
            ->orderBy('nama_departemen')
            ->get();

        return view('admin.users.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
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

        $fotoPath = $request->hasFile('foto')
            ? $request->file('foto')->store('profiles', 'public')
            : null;

        DB::transaction(function () use ($validated, $department, $position, $fotoPath) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'user',
            ]);

            UserProfile::create([
                'user_id' => $user->id,
                'no_hp' => $validated['no_hp'],
                'alamat' => $validated['alamat'],
                'tanggal_lahir' => $validated['tanggal_lahir'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'nik' => $validated['nik'] ?? null,
                'foto' => $fotoPath,
            ]);

            EmployeeDetail::create([
                'user_id' => $user->id,
                'department_id' => $department->id,
                'unit_id' => $validated['unit_id'],
                'position_id' => $validated['position_id'],
                'nip' => $validated['nip'],
                'departemen' => $department->nama_departemen,
                'jabatan' => $position->nama_jabatan,
                'status_kerja' => $validated['status_kerja'],
            ]);
        });

        return redirect()->route('admin.users.index')
            ->with('success', 'Akun pegawai dan biodata berhasil dibuat');
    }

    // ================= EDIT =================
    public function edit($id)
    {
        $user = User::query()->where('role', 'user')->findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::query()->where('role', 'user')->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        // 🔥 hanya update password kalau diisi
        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil diupdate');
    }

    // ================= DELETE =================
    public function destroy($id)
    {
        $user = User::query()->where('role', 'user')->findOrFail($id);

        // 🔥 CEGAH HAPUS DIRI SENDIRI
        if ($user->id == auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri');
        }

        $user->delete();

        return back()->with('success', 'User berhasil dihapus');
    }
}
