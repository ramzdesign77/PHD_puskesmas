<?php

namespace Tests\Feature\Api;

use App\Models\Desa;
use App\Models\MobileAccessToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_accepts_email_and_returns_multi_role_token(): void
    {
        $user = $this->createUser([
            'email' => 'multi-role@example.test',
            'username' => 'multi_role',
            'role' => 'admin,petugas',
            'roles' => ['admin', 'petugas'],
        ]);

        $response = $this->postJson('/api/v1/login', [
            'login' => 'multi-role@example.test',
            'password' => 'secret123',
        ]);

        $token = $response->json('data.token');
        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Login berhasil')
            ->assertJsonPath('data.user.id', $user->id_user)
            ->assertJsonPath('data.user.roles', ['admin', 'petugas']);
        $this->assertDatabaseHas('app_mobile_tokens', [
            'id_user' => $user->id_user,
            'token_hash' => hash('sha256', $token),
        ]);
    }

    public function test_login_accepts_username_and_returns_401_for_invalid_credentials(): void
    {
        $user = $this->createUser([
            'email' => 'warga@example.test',
            'username' => 'warga01',
            'role' => 'warga',
        ]);

        $response = $this->postJson('/api/v1/login', [
            'username' => 'warga01',
            'password' => 'secret123',
        ]);

        $token = $response->json('data.token');
        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.username', 'warga01');
        $this->assertDatabaseHas('app_mobile_tokens', [
            'id_user' => $user->id_user,
            'token_hash' => hash('sha256', $token),
        ]);

        $this->postJson('/api/v1/login', [
            'identifier' => 'warga01',
            'password' => 'wrong-password',
        ])->assertUnauthorized()
            ->assertJsonPath('success', false);

        $this->assertDatabaseCount('app_mobile_tokens', 1);
    }

    public function test_register_warga_stores_required_profile_fields_and_forces_warga_role(): void
    {
        $village = Desa::query()->forceCreate(['nama_desa' => 'Ajung']);

        $response = $this->postJson('/api/v1/register-warga', [
            'nama_lengkap' => 'Warga Ajung',
            'username' => 'warga_ajung',
            'email' => 'warga.ajung@example.test',
            'password' => 'secret123',
            'nik' => '3509010101010001',
            'no_telepon' => '081234567890',
            'alamat' => 'Desa Ajung',
            'id_desa' => $village->id_desa,
            'role' => 'admin',
            'roles' => ['admin'],
        ]);

        $user = User::query()->where('username', 'warga_ajung')->firstOrFail();
        $token = $response->json('data.token');
        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.roles', ['warga']);
        $this->assertSame('warga', $user->role);
        $this->assertSame(['warga'], $user->roles);
        $this->assertSame($village->id_desa, $user->id_desa);
        $this->assertSame('warga_ajung', $user->name);
        $this->assertTrue(Hash::check('secret123', $user->password));
        $this->assertDatabaseHas('app_mobile_tokens', [
            'id_user' => $user->id_user,
            'token_hash' => hash('sha256', $token),
        ]);
    }

    public function test_register_warga_rejects_missing_required_fields(): void
    {
        $this->postJson('/api/v1/register-warga', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'nama_lengkap',
                'username',
                'email',
                'password',
                'nik',
                'no_telepon',
                'alamat',
                'id_desa',
            ]);

        $this->assertDatabaseCount('app_users', 0);
        $this->assertDatabaseCount('app_mobile_tokens', 0);
    }

    public function test_logout_requires_a_valid_token_and_deletes_only_that_token(): void
    {
        $user = $this->createUser([
            'email' => 'logout@example.test',
            'username' => 'logout_user',
            'role' => 'warga',
        ]);
        $token = 'known-mobile-token';

        DB::table('app_mobile_tokens')->insert([
            'id_user' => $user->id_user,
            'name' => 'flutter-mobile',
            'token_hash' => hash('sha256', $token),
            'created_at' => now(),
        ]);

        $this->postJson('/api/v1/logout')->assertUnauthorized();

        $this->withToken($token)->postJson('/api/v1/logout')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Logout berhasil.');

        $this->assertDatabaseMissing('app_mobile_tokens', [
            'token_hash' => hash('sha256', $token),
        ]);
    }

    public function test_authenticated_user_updates_only_their_own_profile(): void
    {
        $user = $this->createUser([
            'nama_lengkap' => 'Warga Lama',
            'username' => 'warga_lama',
            'email' => 'warga-lama@example.test',
            'no_telepon' => '081111111111',
        ]);
        $otherUser = $this->createUser([
            'nama_lengkap' => 'Akun Lain',
            'username' => 'akun_lain',
            'email' => 'akun-lain@example.test',
            'no_telepon' => '082222222222',
        ]);
        $token = 'profile-token';
        $this->createMobileToken($user, $token);

        $this->withToken($token)->putJson('/api/v1/profile', [
            'id_user' => $otherUser->id_user,
            'role' => 'admin',
            'nama_lengkap' => 'Warga Baru',
            'username' => 'warga_baru',
            'email' => 'warga-baru@example.test',
            'no_telepon' => '081234567890',
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Profil berhasil diperbarui.')
            ->assertJsonPath('data.user.id', $user->id_user)
            ->assertJsonPath('data.user.nama_lengkap', 'Warga Baru')
            ->assertJsonPath('data.user.username', 'warga_baru')
            ->assertJsonPath('data.user.email', 'warga-baru@example.test')
            ->assertJsonPath('data.user.no_telepon', '081234567890');

        $this->assertDatabaseHas('app_users', [
            'id_user' => $user->id_user,
            'nama_lengkap' => 'Warga Baru',
            'username' => 'warga_baru',
            'email' => 'warga-baru@example.test',
            'no_telepon' => '081234567890',
            'role' => 'warga',
        ]);
        $this->assertDatabaseHas('app_users', [
            'id_user' => $otherUser->id_user,
            'nama_lengkap' => 'Akun Lain',
            'username' => 'akun_lain',
            'email' => 'akun-lain@example.test',
            'no_telepon' => '082222222222',
            'role' => 'warga',
        ]);
    }

    public function test_profile_update_returns_401_without_a_valid_mobile_token(): void
    {
        $user = $this->createUser([
            'email' => 'profile-auth@example.test',
            'username' => 'profile_auth',
        ]);

        $this->putJson('/api/v1/profile', [
            'nama_lengkap' => 'Tidak Berubah',
            'username' => 'profile_auth',
            'email' => 'profile-auth@example.test',
        ])->assertUnauthorized()
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('app_users', [
            'id_user' => $user->id_user,
            'nama_lengkap' => 'Test User',
        ]);
    }

    public function test_profile_update_allows_the_authenticated_users_existing_username_and_email(): void
    {
        $user = $this->createUser([
            'nama_lengkap' => 'Warga Lama',
            'username' => 'warga_lama',
            'email' => 'warga-lama@example.test',
        ]);
        $token = 'existing-profile-token';
        $this->createMobileToken($user, $token);

        $this->withToken($token)->putJson('/api/v1/profile', [
            'nama_lengkap' => 'Warga Diperbarui',
            'username' => $user->username,
            'email' => $user->email,
            'no_telepon' => null,
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.username', 'warga_lama')
            ->assertJsonPath('data.user.email', 'warga-lama@example.test');

        $this->assertDatabaseHas('app_users', [
            'id_user' => $user->id_user,
            'nama_lengkap' => 'Warga Diperbarui',
            'name' => 'warga_lama',
            'username' => 'warga_lama',
            'email' => 'warga-lama@example.test',
            'no_telepon' => null,
        ]);
    }

    public function test_profile_update_rejects_duplicate_username_and_email(): void
    {
        $user = $this->createUser([
            'nama_lengkap' => 'Warga Lama',
            'username' => 'warga_lama',
            'email' => 'warga-lama@example.test',
        ]);
        $otherUser = $this->createUser([
            'nama_lengkap' => 'Akun Lain',
            'username' => 'akun_lain',
            'email' => 'akun-lain@example.test',
        ]);
        $token = 'duplicate-profile-token';
        $this->createMobileToken($user, $token);

        $this->withToken($token)->putJson('/api/v1/profile', [
            'nama_lengkap' => 'Warga Baru',
            'username' => $otherUser->username,
            'email' => $otherUser->email,
            'no_telepon' => null,
        ])->assertUnprocessable()
            ->assertJsonStructure(['message', 'errors' => ['username', 'email']])
            ->assertJsonValidationErrors(['username', 'email']);

        $this->assertDatabaseHas('app_users', [
            'id_user' => $user->id_user,
            'nama_lengkap' => 'Warga Lama',
            'username' => 'warga_lama',
            'email' => 'warga-lama@example.test',
        ]);
    }

    public function test_profile_update_rejects_invalid_field_values(): void
    {
        $user = $this->createUser([
            'email' => 'invalid-profile@example.test',
            'username' => 'invalid_profile',
        ]);
        $token = 'invalid-profile-token';
        $this->createMobileToken($user, $token);

        $this->withToken($token)->putJson('/api/v1/profile', [
            'nama_lengkap' => str_repeat('a', 151),
            'username' => 'not a username',
            'email' => 'not-an-email',
            'no_telepon' => str_repeat('1', 21),
        ])->assertUnprocessable()
            ->assertJsonStructure([
                'message',
                'errors' => ['nama_lengkap', 'username', 'email', 'no_telepon'],
            ])
            ->assertJsonValidationErrors(['nama_lengkap', 'username', 'email', 'no_telepon']);

        $this->assertDatabaseHas('app_users', [
            'id_user' => $user->id_user,
            'nama_lengkap' => 'Test User',
            'username' => 'invalid_profile',
            'email' => 'invalid-profile@example.test',
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createUser(array $attributes): User
    {
        return User::query()->create(array_merge([
            'nama_lengkap' => 'Test User',
            'username' => 'test_user',
            'email' => 'test@example.test',
            'password' => 'secret123',
            'role' => 'warga',
            'is_active' => true,
        ], $attributes));
    }

    private function createMobileToken(User $user, string $plainToken): void
    {
        MobileAccessToken::query()->create([
            'id_user' => $user->id_user,
            'name' => 'flutter-mobile',
            'token_hash' => hash('sha256', $plainToken),
            'created_at' => now(),
        ]);
    }
}
