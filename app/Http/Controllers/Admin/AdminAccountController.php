<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminAccountController extends Controller
{
    public function index(Request $request)
    {
        $admins = User::query()
            ->where('role', 'admin')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('username', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->get();

        return view('admin.admin_accounts.index', compact('admins'));
    }

    public function create()
    {
        return view('admin.admin_accounts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        User::create([
            'name' => $validated['name'],
            'username' => strtolower(trim($validated['username'])),
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
        ]);

        return redirect()->route('admin.settings.admin_accounts.index')
            ->with('success', 'Akun admin berhasil dibuat.');
    }

    public function edit(User $adminAccount)
    {
        abort_unless($adminAccount->role === 'admin', 404);

        return view('admin.admin_accounts.edit', [
            'admin' => $adminAccount,
        ]);
    }

    public function update(Request $request, User $adminAccount)
    {
        abort_unless($adminAccount->role === 'admin', 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('users', 'username')->ignore($adminAccount->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($adminAccount->id)],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $data = [
            'name' => $validated['name'],
            'username' => strtolower(trim($validated['username'])),
            'email' => $validated['email'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $adminAccount->update($data);

        return redirect()->route('admin.settings.admin_accounts.index')
            ->with('success', 'Akun admin berhasil diperbarui.');
    }

    public function destroy(User $adminAccount)
    {
        abort_unless($adminAccount->role === 'admin', 404);

        if ($adminAccount->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun admin yang sedang dipakai.');
        }

        $adminAccount->delete();

        return back()->with('success', 'Akun admin berhasil dihapus.');
    }
}
