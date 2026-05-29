<?php

namespace App\Http\Controllers;

use App\Models\EmployeeDetail;
use App\Models\FaceEmbedding;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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
            'cf-turnstile-response' => [$this->turnstileEnabled() ? 'required' : 'nullable', 'string'],
        ], [
            'login.required' => 'NIP atau username wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
            'cf-turnstile-response.required' => 'Verifikasi keamanan wajib diselesaikan.',
        ]);

        if ($this->turnstileEnabled() && !$this->validateTurnstile(
            $request->input('cf-turnstile-response'),
            $request->ip()
        )) {
            return back()
                ->withErrors(['cf-turnstile-response' => 'Verifikasi keamanan gagal. Silakan coba lagi.'])
                ->withInput($request->only('login', 'remember', 'redirect_to'));
        }

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

        if (!$user) {
            return back()
                ->withErrors(['login' => 'Akun user tidak terdaftar. Periksa kembali NIP/username atau hubungi admin.'])
                ->withInput($request->only('login'));
        }

        if ($user->role !== 'user') {
            return back()
                ->withErrors(['login' => 'Akun ini bukan akun pegawai. Gunakan halaman login yang sesuai.'])
                ->withInput($request->only('login'));
        }

        if (!Hash::check($validated['password'], $user->password)) {
            return back()
                ->withErrors(['password' => 'Kata sandi Anda salah.'])
                ->withInput($request->only('login'));
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if (($validated['redirect_to'] ?? null) === 'face.enroll') {
            return redirect()->route('face.enroll');
        }

        if (!FaceEmbedding::where('user_id', Auth::id())->exists()) {
            return redirect()->route('face.enroll');
        }

        return redirect('/dashboard');
    }

    private function validateTurnstile(?string $token, ?string $remoteIp = null): bool
    {
        if (!$token) {
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->post(config('services.turnstile.verify_url'), [
                    'secret' => config('services.turnstile.secret_key'),
                    'response' => $token,
                    'remoteip' => $remoteIp,
                ]);

            if (!$response->ok()) {
                Log::warning('Turnstile verification request failed.', [
                    'status' => $response->status(),
                ]);

                return false;
            }

            $payload = $response->json();

            return (bool) ($payload['success'] ?? false);
        } catch (\Throwable $e) {
            Log::warning('Turnstile verification exception.', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function turnstileEnabled(): bool
    {
        return (bool) config('services.turnstile.enabled')
            && filled(config('services.turnstile.site_key'))
            && filled(config('services.turnstile.secret_key'));
    }

    public function userRegister(Request $request)
    {
        return redirect()
            ->route('register')
            ->with('info', 'Pendaftaran mandiri dinonaktifkan. Akun pegawai dibuat oleh admin rumah sakit.');
    }

    public function showAdminLogin()
    {
        return view('auth.admin-login');
    }

    public function adminLogin(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $email = strtolower(trim($validated['email']));
        $user = User::where('email', $email)->first();

        if (!$user) {
            return back()
                ->withErrors(['email' => 'Email admin yang Anda masukkan salah atau tidak terdaftar.'])
                ->withInput($request->only('email'));
        }

        if ($user->role !== 'admin') {
            return back()
                ->withErrors(['email' => 'Email ini bukan akun admin.'])
                ->withInput($request->only('email'));
        }

        if (!Hash::check($validated['password'], $user->password)) {
            return back()
                ->withErrors(['password' => 'Password yang Anda masukkan salah.'])
                ->withInput($request->only('email'));
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/admin/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
