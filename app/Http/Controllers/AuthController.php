<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();

            return redirect()->route($user->isAdmin() ? 'dashboard.admin' : 'dashboard.petugas');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:150'],
            'password' => ['required', 'string', 'min:4'],
        ]);

        $identifier = trim($validated['username']);
        $identityField = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $credentials = [
            $identityField => $identifier,
            'password' => $validated['password'],
        ];

        if (! Auth::attempt($credentials)) {
            return back()
                ->withInput($request->only('username'))
                ->withErrors(['username' => 'Username atau kata sandi tidak sesuai.']);
        }

        $request->session()->regenerate();
        $user = Auth::user();

        if ($user->is_active === false) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withInput($request->only('username'))
                ->withErrors(['username' => 'Akun ini sedang dinonaktifkan.']);
        }

        $request->session()->put([
            'role' => $this->mapRole($user->role),
            'user_id' => $user->getAuthIdentifier(),
            'user_name' => $user->nama_lengkap,
            'username' => $user->username,
        ]);

        $dashboardRoute = $user->isAdmin() ? 'dashboard.admin' : 'dashboard.petugas';

        return redirect()->intended(route($dashboardRoute));
    }

    private function mapRole(string $role): string
    {
        return match ($role) {
            'admin', 'kepala_puskesmas' => 'admin',
            'petugas', 'sanitarian', 'staf_backup_kluster4' => 'officer',
            default => 'citizen',
        };
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil logout.');
    }
}
