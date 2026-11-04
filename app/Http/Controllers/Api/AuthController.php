<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MobileAccessToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        foreach (['login', 'username', 'email', 'identifier'] as $field) {
            if ($request->filled($field)) {
                $request->merge(['login' => $request->input($field)]);
                break;
            }
        }

        $validated = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $identifier = trim($validated['login']);
        $user = User::query()
            ->where(function ($query) use ($identifier): void {
                $query->where('email', strtolower($identifier))
                    ->orWhere('username', $identifier);
            })
            ->first();

        if ($user === null || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email/username atau kata sandi tidak sesuai.',
            ], 401);
        }

        if (! $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun ini sedang dinonaktifkan.',
            ], 403);
        }

        return DB::transaction(fn (): JsonResponse => $this->issueToken($user, 'Login berhasil', 200));
    }

    public function registerWarga(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'username' => ['required', 'string', 'min:3', 'max:50', 'alpha_dash', Rule::unique('app_users', 'username')],
            'email' => ['required', 'email', 'max:255', Rule::unique('app_users', 'email')],
            'password' => ['required', 'string', 'min:8'],
            'nik' => ['required', 'digits:16'],
            'no_telepon' => ['required', 'string', 'max:20'],
            'alamat' => ['required', 'string', 'max:500'],
            'id_desa' => ['required', 'integer', 'exists:ref_desa,id_desa'],
        ]);

        return DB::transaction(function () use ($validated): JsonResponse {
            $user = User::query()->create([
                'nama_lengkap' => trim($validated['nama_lengkap']),
                'name' => trim($validated['username']),
                'username' => trim($validated['username']),
                'email' => strtolower(trim($validated['email'])),
                'password' => $validated['password'],
                'no_telepon' => trim($validated['no_telepon']),
                'alamat' => trim($validated['alamat']),
                'id_desa' => $validated['id_desa'],
                'role' => 'warga',
                'roles' => ['warga'],
                'is_active' => true,
            ]);

            return $this->issueToken($user, 'Pendaftaran warga berhasil.', 201);
        });
    }

    public function logout(Request $request): JsonResponse
    {
        MobileAccessToken::query()
            ->where('token_hash', hash('sha256', (string) $request->bearerToken()))
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
            'data' => null,
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (is_string($request->input('username'))) {
            $request->merge(['username' => trim($request->input('username'))]);
        }

        if (is_string($request->input('email'))) {
            $request->merge(['email' => strtolower(trim($request->input('email')))]);
        }

        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'alpha_dash',
                Rule::unique('app_users', 'username')->ignore($user->id_user, 'id_user'),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('app_users', 'email')->ignore($user->id_user, 'id_user'),
            ],
            'no_telepon' => ['sometimes', 'nullable', 'string', 'max:20'],
        ]);

        $profile = [
            'nama_lengkap' => trim($validated['nama_lengkap']),
            'name' => $validated['username'],
            'username' => $validated['username'],
            'email' => $validated['email'],
        ];

        if (array_key_exists('no_telepon', $validated)) {
            $profile['no_telepon'] = $validated['no_telepon'];
        }

        $user->fill($profile)->save();

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui.',
            'data' => [
                'user' => [
                    'id' => $user->id_user,
                    'nama_lengkap' => $user->nama_lengkap,
                    'username' => $user->username,
                    'email' => $user->email,
                    'no_telepon' => $user->no_telepon,
                ],
            ],
        ]);
    }

    private function issueToken(User $user, string $message, int $status): JsonResponse
    {
        $plainToken = Str::random(80);

        MobileAccessToken::query()->create([
            'id_user' => $user->id_user,
            'name' => 'flutter-mobile',
            'token_hash' => hash('sha256', $plainToken),
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'token' => $plainToken,
                'user' => [
                    'id' => $user->id_user,
                    'nama_lengkap' => $user->nama_lengkap,
                    'username' => $user->username,
                    'email' => $user->email,
                    'roles' => $user->getRolesListAttribute(),
                ],
            ],
        ], $status);
    }
}
