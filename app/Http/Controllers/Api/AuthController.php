<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MobileAccessToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

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

    public function profile(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => ['user' => $this->profileData($request->user())],
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
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'hapus_foto' => ['sometimes', 'boolean'],
        ]);

        $removePhoto = $request->boolean('hapus_foto');

        if ($removePhoto && $request->hasFile('foto')) {
            throw ValidationException::withMessages([
                'foto' => 'Pilih unggah foto atau hapus foto dalam satu permintaan.',
            ]);
        }

        $profile = [
            'nama_lengkap' => trim($validated['nama_lengkap']),
            'name' => $validated['username'],
            'username' => $validated['username'],
            'email' => $validated['email'],
        ];

        if (array_key_exists('no_telepon', $validated)) {
            $profile['no_telepon'] = $validated['no_telepon'];
        }

        if ($removePhoto) {
            $profile['foto'] = null;
        }

        $photoPath = null;
        $previousPhoto = null;

        try {
            if ($request->hasFile('foto')) {
                $photoPath = $request->file('foto')->store('petugas', 'public');
                abort_if($photoPath === false, 500, 'Foto profil gagal disimpan.');
                $profile['foto'] = $photoPath;
            }

            $user = DB::transaction(function () use ($user, $profile, &$previousPhoto): User {
                $account = User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();
                $previousPhoto = $account->foto;
                $account->fill($profile)->save();

                return $account;
            });
        } catch (Throwable $exception) {
            if (is_string($photoPath)) {
                $this->deleteProfilePhoto($photoPath);
            }

            throw $exception;
        }

        if ((is_string($photoPath) || $removePhoto) && filled($previousPhoto) && $previousPhoto !== $photoPath) {
            $this->deleteProfilePhoto($previousPhoto);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui.',
            'data' => [
                'user' => $this->profileData($user),
            ],
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed', 'different:current_password'],
        ], [
            'current_password.required' => 'Password lama wajib diisi.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.max' => 'Password baru maksimal 72 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak sesuai.',
            'password.different' => 'Password baru harus berbeda dari password lama.',
        ]);

        if (strlen($validated['password']) > 72) {
            throw ValidationException::withMessages([
                'password' => 'Password baru maksimal 72 byte; simbol tertentu memakai lebih dari satu byte.',
            ]);
        }

        DB::transaction(function () use ($request, $validated): void {
            $user = User::query()->whereKey($request->user()->getKey())->lockForUpdate()->firstOrFail();

            if (! Hash::check($validated['current_password'], $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => 'Password lama tidak sesuai.',
                ]);
            }

            $user->password = $validated['password'];
            $user->save();

            MobileAccessToken::query()
                ->where('id_user', $user->id_user)
                ->where('token_hash', '!=', hash('sha256', (string) $request->bearerToken()))
                ->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diperbarui. Sesi mobile lainnya telah keluar.',
            'data' => null,
        ]);
    }

    /** @return array<string, mixed> */
    private function profileData(User $user): array
    {
        return [
            'id' => $user->id_user,
            'nama_lengkap' => $user->nama_lengkap,
            'username' => $user->username,
            'email' => $user->email,
            'no_telepon' => $user->no_telepon,
            'roles' => $user->getRolesListAttribute(),
            'foto' => $user->foto,
            'foto_url' => filled($user->foto) ? Storage::disk('public')->url($user->foto) : null,
        ];
    }

    private function deleteProfilePhoto(string $path): void
    {
        try {
            if (! Storage::disk('public')->delete($path)) {
                report(new \RuntimeException('Foto profil lama atau sementara gagal dihapus.'));
            }
        } catch (Throwable $exception) {
            report($exception);
        }
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
                'user' => $this->profileData($user),
            ],
        ], $status);
    }
}
