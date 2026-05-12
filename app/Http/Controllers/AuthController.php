<?php

namespace App\Http\Controllers;

use App\Models\EmployeeDetail;
use App\Models\FaceEmbedding;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showUserLogin()
    {
        return view('auth.user-login');
    }

    public function userLogin(Request $request)
    {
        $validated = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
            'redirect_to' => ['nullable', 'string'],
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

        if (!$user || $user->role !== 'user' || !Hash::check($validated['password'], $user->password)) {
            return back()
                ->withErrors(['login' => 'Login gagal'])
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
