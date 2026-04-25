<?php

namespace App\Http\Controllers;

use App\Models\FaceEmbedding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showUserLogin()
    {
        return view('auth.user-login');
    }

    public function userLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {

            if (Auth::user()->role !== 'user') {
                Auth::logout();
                return back()->withErrors(['email' => 'Akun bukan user']);
            }

            $request->session()->regenerate();

            if (!FaceEmbedding::where('user_id', Auth::id())->exists()) {
                return redirect()->route('face.enroll');
            }

            return redirect('/dashboard');
        }

        return back()->withErrors(['email' => 'Login gagal']);
    }

    public function showAdminLogin()
    {
        return view('auth.admin-login');
    }

    public function adminLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
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
