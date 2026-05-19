<?php

namespace App\Http\Controllers;

use App\Models\EmployeeDetail;
use App\Models\FaceEmbedding;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function showUserLogin()
    {
        return view('auth.user-login');
    }

    public function showUserRegister()
    {
        return view('auth.user-register');
    }

    public function userLogin(Request $request)
    {
        $validated = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
            'redirect_to' => ['nullable', 'string'],
        ], [
            'login.required' => 'NIP atau username wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $login = trim($validated['login']);
        $normalizedLogin = strtolower($login);

        $user = User::query()
            ->where('role', 'user')
            ->where('username', $normalizedLogin)
            ->first();

        if (!$user) {
            $employee = EmployeeDetail::with('user')
                ->where('nip', $login)
                ->first();

            $user = $employee?->user;
        }

        if (!$user || $user->role !== 'user') {
            return back()
                ->withErrors(['login' => 'NIP atau username salah.'])
                ->withInput($request->only('login'));
        }

        if (!Hash::check($validated['password'], $user->password)) {
            return back()
                ->withErrors(['password' => 'Kata sandi Anda salah.'])
                ->withInput($request->only('login'));
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if ($validated['redirect_to'] ?? null === 'face.enroll') {
            return redirect()->route('face.enroll');
        }

        if (!FaceEmbedding::where('user_id', Auth::id())->exists()) {
            return redirect()->route('face.enroll');
        }

        return redirect('/dashboard');
    }

    public function userRegister(Request $request)
    {
        $request->merge([
            'username' => strtolower(trim((string) $request->input('username'))),
            'email' => strtolower(trim((string) $request->input('email'))),
            'nip' => trim((string) $request->input('nip')),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:users,username'],
            'nip' => ['required', 'string', 'max:50', Rule::unique('employee_details', 'nip')],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, strip, dan underscore.',
            'username.unique' => 'Username sudah digunakan.',
            'nip.required' => 'NIP wajib diisi.',
            'nip.unique' => 'NIP sudah terdaftar.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sama.',
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'user',
            ]);

            EmployeeDetail::create([
                'user_id' => $user->id,
                'nip' => $validated['nip'],
                'departemen' => '-',
                'jabatan' => '-',
                'status_kerja' => 'tetap',
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('face.enroll');
    }

    public function showAdminLogin()
    {
        return view('auth.admin-login');
    }

    public function adminLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            if (Auth::user()->role !== 'admin') {
                Auth::logout();

                return back()->withErrors(['email' => 'Akses hanya untuk admin']);
            }

            $request->session()->regenerate();

            return redirect('/admin/dashboard');
        }

        return back()->withErrors(['email' => 'Login gagal']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
