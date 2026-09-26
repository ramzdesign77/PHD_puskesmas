<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
            'role' => 'required|in:citizen,officer,admin',
            'email' => 'required|email',
            'password' => 'required|min:4',
        ]);

        $names = [
            'citizen' => 'Budi Santoso',
            'officer' => 'Petugas Sari Dewi',
            'admin'   => 'dr. Kepala Puskesmas',
        ];

        session([
            'role'       => $request->role,
            'user_name'  => $names[$request->role],
            'user_email' => $request->email,
        ]);

        return redirect()->route('dashboard');
    }

    public function logout()
    {
        session()->flush();
        return redirect()->route('login')->with('success', 'Anda telah berhasil logout.');
    }
}
