<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session()->has('role')) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string|min:4',
        ]);

        $user = DB::table('users')
            ->where('username', $request->string('username')->toString())
            ->where(function ($query) {
                $query->where('is_active', true)->orWhereNull('is_active');
            })
            ->first();

        $demoUsers = [
            'admin' => ['password' => 'password', 'name' => 'dr. Kepala Puskesmas', 'role' => 'admin'],
            'petugas' => ['password' => 'password', 'name' => 'Petugas Sari Dewi', 'role' => 'officer'],
            'masyarakat' => ['password' => 'password', 'name' => 'Budi Santoso', 'role' => 'citizen'],
        ];

        $username = $request->string('username')->toString();
        $password = $request->string('password')->toString();
        $authenticated = $user && Hash::check($password, $user->password);

        if (!$user && isset($demoUsers[$username])) {
            $demoUser = $demoUsers[$username];
            $authenticated = Hash::check($password, Hash::make($demoUser['password']));
            $user = (object) [
                'nama_lengkap' => $demoUser['name'],
                'role' => $demoUser['role'],
                'username' => $username,
            ];
        }

        if (!$authenticated) {
            return back()
                ->withInput($request->only('username'))
                ->withErrors(['username' => 'Username atau kata sandi tidak sesuai.']);
        }

        session([
            'role'       => $this->mapRole($user->role),
            'user_name'  => $user->nama_lengkap,
            'username'   => $user->username,
        ]);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    private function mapRole(string $role): string
    {
        return match ($role) {
            'admin', 'kepala_puskesmas' => 'admin',
            'sanitarian', 'staf_backup_kluster4' => 'officer',
            default => 'citizen',
        };
    }

    public function logout()
    {
        session()->flush();
        return redirect()->route('login')->with('success', 'Anda telah berhasil logout.');
    }
}
