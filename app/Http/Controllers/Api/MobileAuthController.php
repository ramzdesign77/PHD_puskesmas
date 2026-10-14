<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MobileAuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:30'],
            'username' => ['required', 'string', 'min:3', 'max:25', 'alpha_dash', Rule::unique('users', 'username')->whereNull('deleted_at')],
            'email' => ['required', 'email', 'max:30', Rule::unique('users', 'email')->whereNull('deleted_at')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'nama_lengkap' => trim($validated['nama_lengkap']),
            'username' => trim($validated['username']),
            'email' => strtolower(trim($validated['email'])),
            'password' => $validated['password'],
            // Registrasi publik hanya dapat membuat akun warga, bukan admin/petugas.
            'role' => 'warga',
            'is_active' => true,
        ]);

        return $this->issueToken($user, 'Registrasi berhasil.', 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'identifier' => ['required', 'string', 'max:150'],
            'password' => ['required', 'string'],
        ]);

        $identifier = trim($validated['identifier']);
        $query = User::query();
        $query->where(filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username', $identifier);
        $user = $query->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Username/email atau kata sandi tidak sesuai.'], 422);
        }

        if ($user->is_active === false) {
            return response()->json(['message' => 'Akun ini sedang dinonaktifkan.'], 403);
        }

        return $this->issueToken($user, 'Login berhasil.', 200);
    }

    public function logout(Request $request): JsonResponse
    {
        $plainToken = $request->bearerToken();
        if ($plainToken) {
            DB::table('mobile_access_tokens')->where('token_hash', hash('sha256', $plainToken))->delete();
        }
        return response()->json(['message' => 'Logout berhasil.']);
    }

    private function issueToken(User $user, string $message, int $status): JsonResponse
    {
        $plainToken = Str::random(80);
        DB::table('mobile_access_tokens')->insert([
            'id_user' => $user->id_user,
            'name' => 'flutter-mobile',
            'token_hash' => hash('sha256', $plainToken),
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => $message,
            'token' => $plainToken,
            'user' => [
                'id_user' => $user->id_user,
                'nama_lengkap' => $user->nama_lengkap,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ], $status);
    }
}
