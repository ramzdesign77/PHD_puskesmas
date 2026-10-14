<?php

namespace Tests\Feature\Api;

use App\Models\Desa;
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
        $this->assertDatabaseHas('mobile_access_tokens', [
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
        $this->assertDatabaseHas('mobile_access_tokens', [
            'id_user' => $user->id_user,
            'token_hash' => hash('sha256', $token),
        ]);

        $this->postJson('/api/v1/login', [
            'identifier' => 'warga01',
            'password' => 'wrong-password',
        ])->assertUnauthorized()
            ->assertJsonPath('success', false);

        $this->assertDatabaseCount('mobile_access_tokens', 1);
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
        $this->assertTrue(Hash::check('secret123', $user->password));
        $this->assertDatabaseHas('mobile_access_tokens', [
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

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('mobile_access_tokens', 0);
    }

    public function test_logout_requires_a_valid_token_and_deletes_only_that_token(): void
    {
        $user = $this->createUser([
            'email' => 'logout@example.test',
            'username' => 'logout_user',
            'role' => 'warga',
        ]);
        $token = 'known-mobile-token';

        DB::table('mobile_access_tokens')->insert([
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

        $this->assertDatabaseMissing('mobile_access_tokens', [
            'token_hash' => hash('sha256', $token),
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
}
