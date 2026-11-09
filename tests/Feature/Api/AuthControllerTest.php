<?php

namespace Tests\Feature\Api;

use App\Models\Desa;
use App\Models\MobileAccessToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\TestWith;
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

    public function test_authenticated_user_can_save_and_read_their_default_address(): void
    {
        $user = $this->createUser([
            'nama_lengkap' => 'Warga Alamat',
            'username' => 'warga_alamat',
            'email' => 'warga-alamat@example.test',
        ]);
        $otherUser = $this->createUser([
            'username' => 'alamat_lain',
            'email' => 'alamat-lain@example.test',
        ]);
        $village = Desa::query()->forceCreate(['nama_desa' => 'Karangrejo']);
        $token = 'saved-address-token';
        $this->createMobileToken($user, $token);

        $payload = [
            'id_user' => $otherUser->id_user,
            'nama_lengkap' => $user->nama_lengkap,
            'username' => $user->username,
            'email' => $user->email,
            'no_telepon' => '081234567890',
            'id_desa' => $village->id_desa,
            'rt' => '05',
            'rw' => '03',
            'alamat_detail' => 'Belakang balai desa',
        ];

        $this->withToken($token)->putJson('/api/v1/profile', $payload)
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id_user)
            ->assertJsonPath('data.user.id_desa', $village->id_desa)
            ->assertJsonPath('data.user.nama_desa', 'Karangrejo')
            ->assertJsonPath('data.user.rt', '05')
            ->assertJsonPath('data.user.rw', '03')
            ->assertJsonPath('data.user.alamat_detail', 'Belakang balai desa');

        $this->assertDatabaseHas('app_users', [
            'id_user' => $user->id_user,
            'id_desa' => $village->id_desa,
            'rt' => '05',
            'rw' => '03',
            'alamat_detail' => 'Belakang balai desa',
        ]);
        $this->assertDatabaseHas('app_users', [
            'id_user' => $otherUser->id_user,
            'id_desa' => null,
            'rt' => null,
            'rw' => null,
            'alamat_detail' => null,
        ]);

        $this->withToken($token)->getJson('/api/v1/profile')
            ->assertOk()
            ->assertJsonPath('data.user.id_desa', $village->id_desa)
            ->assertJsonPath('data.user.nama_desa', 'Karangrejo')
            ->assertJsonPath('data.user.rt', '05')
            ->assertJsonPath('data.user.rw', '03')
            ->assertJsonPath('data.user.alamat_detail', 'Belakang balai desa');
    }

    public function test_saved_address_update_rejects_unknown_village_and_invalid_fields(): void
    {
        $user = $this->createUser([
            'username' => 'alamat_invalid',
            'email' => 'alamat-invalid@example.test',
        ]);
        $this->createMobileToken($user, 'invalid-saved-address-token');

        $this->withToken('invalid-saved-address-token')->putJson('/api/v1/profile', [
            'nama_lengkap' => $user->nama_lengkap,
            'username' => $user->username,
            'email' => $user->email,
            'id_desa' => 999999,
            'rt' => str_repeat('1', 11),
            'rw' => '',
            'alamat_detail' => str_repeat('a', 256),
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['id_desa', 'rt', 'rw', 'alamat_detail']);

        $this->assertDatabaseHas('app_users', [
            'id_user' => $user->id_user,
            'id_desa' => null,
            'rt' => null,
            'rw' => null,
            'alamat_detail' => null,
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

    #[TestWith(['warga'])]
    #[TestWith(['petugas'])]
    public function test_profile_photo_is_saved_and_read_for_the_authenticated_account(string $role): void
    {
        Storage::fake('public');
        config(['filesystems.disks.public.url' => 'http://example.test/storage']);
        Storage::disk('public')->put('petugas/old.jpg', 'old-photo');
        $user = $this->createUser(['role' => $role, 'foto' => 'petugas/old.jpg']);
        $other = $this->createUser(['username' => 'other', 'email' => 'other@example.test']);
        $this->createMobileToken($user, 'photo-token');

        $response = $this->withToken('photo-token')->post('/api/v1/profile', [
            '_method' => 'PUT',
            'nama_lengkap' => $user->nama_lengkap,
            'username' => $user->username,
            'email' => $user->email,
            'id_user' => $other->id_user,
            'role' => 'admin',
            'foto' => UploadedFile::fake()->image('profile.jpg'),
        ], ['Accept' => 'application/json']);

        $response->assertOk()->assertJsonPath('data.user.id', $user->id_user);
        $path = $response->json('data.user.foto');
        Storage::disk('public')->assertExists($path);
        Storage::disk('public')->assertMissing('petugas/old.jpg');
        $this->assertSame($path, $user->fresh()->foto);
        $this->assertSame($role, $user->fresh()->role);
        $this->assertNull($other->fresh()->foto);

        $this->withToken('photo-token')->getJson('/api/v1/profile')
            ->assertOk()
            ->assertJsonPath('data.user.foto_url', 'http://example.test/storage/'.$path)
            ->assertJsonMissingPath('data.user.password');
        $this->postJson('/api/v1/login', [
            'login' => $user->email,
            'password' => 'secret123',
        ])->assertOk()->assertJsonPath('data.user.foto', $path);
    }

    public function test_profile_without_photo_returns_null_and_requires_authentication(): void
    {
        $this->getJson('/api/v1/profile')->assertUnauthorized();
        $this->withToken('invalid')->getJson('/api/v1/profile')->assertUnauthorized();
        $user = $this->createUser([]);
        $this->createMobileToken($user, 'read-token');
        $this->withToken('read-token')->getJson('/api/v1/profile')
            ->assertOk()->assertJsonPath('data.user.foto_url', null);
        $user->update(['is_active' => false]);
        $this->withToken('read-token')->getJson('/api/v1/profile')->assertForbidden();
    }

    #[TestWith(['invalid'])]
    #[TestWith(['oversized'])]
    public function test_invalid_photo_does_not_replace_the_existing_profile(string $kind): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('petugas/old.jpg', 'old-photo');
        $user = $this->createUser(['foto' => 'petugas/old.jpg']);
        $this->createMobileToken($user, 'invalid-photo-token');
        $photo = $kind === 'invalid'
            ? UploadedFile::fake()->create('document.pdf', 10, 'application/pdf')
            : UploadedFile::fake()->image('large.jpg')->size(5121);

        $this->withToken('invalid-photo-token')->post('/api/v1/profile', [
            '_method' => 'PUT',
            'nama_lengkap' => 'Changed Name',
            'username' => $user->username,
            'email' => $user->email,
            'foto' => $photo,
        ], ['Accept' => 'application/json'])->assertUnprocessable()->assertJsonValidationErrors('foto');

        $this->assertSame('petugas/old.jpg', $user->fresh()->foto);
        $this->assertSame('Test User', $user->fresh()->nama_lengkap);
        Storage::disk('public')->assertExists('petugas/old.jpg');
        $this->assertCount(1, Storage::disk('public')->allFiles());
    }

    public function test_text_only_update_keeps_existing_photo(): void
    {
        $user = $this->createUser(['foto' => 'petugas/existing.jpg']);
        $this->createMobileToken($user, 'keep-photo-token');
        $this->withToken('keep-photo-token')->putJson('/api/v1/profile', [
            'nama_lengkap' => 'Updated Name',
            'username' => $user->username,
            'email' => $user->email,
        ])->assertOk()->assertJsonPath('data.user.foto', 'petugas/existing.jpg');
        $this->assertSame('petugas/existing.jpg', $user->fresh()->foto);
    }

    #[TestWith(['warga'])]
    #[TestWith(['petugas'])]
    public function test_password_change_updates_only_the_owner_and_revokes_other_mobile_sessions(string $role): void
    {
        $user = $this->createUser(['role' => $role, 'roles' => [$role]]);
        $other = $this->createUser(['username' => 'other', 'email' => 'other@example.test']);
        $this->createMobileToken($user, 'current-session');
        $this->createMobileToken($user, 'old-session');
        $this->createMobileToken($other, 'other-user-session');

        $this->withToken('current-session')->putJson('/api/v1/profile/password', [
            'current_password' => 'secret123',
            'password' => 'new-secret456',
            'password_confirmation' => 'new-secret456',
            'id_user' => $other->id_user,
        ])->assertOk()->assertJsonPath('success', true)
            ->assertJsonMissingPath('data.password');

        $this->assertTrue(Hash::check('new-secret456', $user->fresh()->password));
        $this->assertFalse(Hash::check('secret123', $user->fresh()->password));
        $this->assertTrue(Hash::check('secret123', $other->fresh()->password));
        $this->withToken('current-session')->getJson('/api/v1/profile')->assertOk();
        $this->withToken('old-session')->getJson('/api/v1/profile')->assertUnauthorized();
        $this->withToken('other-user-session')->getJson('/api/v1/profile')->assertOk();
        $this->postJson('/api/v1/login', [
            'login' => $user->username, 'password' => 'secret123',
        ])->assertUnauthorized();
        $this->postJson('/api/v1/login', [
            'login' => $user->username, 'password' => 'new-secret456',
        ])->assertOk();
    }

    #[TestWith(['wrong-password', 'new-secret456', 'new-secret456', 'current_password'])]
    #[TestWith(['secret123', 'new-secret456', 'mismatch', 'password'])]
    #[TestWith(['secret123', 'short', 'short', 'password'])]
    #[TestWith(['secret123', 'secret123', 'secret123', 'password'])]
    #[TestWith(['', 'new-secret456', 'new-secret456', 'current_password'])]
    public function test_invalid_password_changes_preserve_password_and_sessions(
        string $current,
        string $password,
        string $confirmation,
        string $field,
    ): void {
        $user = $this->createUser([]);
        $this->createMobileToken($user, 'password-test');
        $this->createMobileToken($user, 'second-session');
        $hash = $user->password;

        $this->withToken('password-test')->putJson('/api/v1/profile/password', [
            'current_password' => $current,
            'password' => $password,
            'password_confirmation' => $confirmation,
        ])->assertUnprocessable()->assertJsonValidationErrors($field);

        $this->assertSame($hash, $user->fresh()->password);
        $this->assertDatabaseCount('app_mobile_tokens', 2);
    }

    public function test_password_endpoint_requires_an_active_authenticated_account(): void
    {
        $this->putJson('/api/v1/profile/password', [])->assertUnauthorized();
        $this->withToken('invalid')->putJson('/api/v1/profile/password', [])->assertUnauthorized();
        $user = $this->createUser(['is_active' => false]);
        $this->createMobileToken($user, 'inactive');
        $this->withToken('inactive')->putJson('/api/v1/profile/password', [])->assertForbidden();
    }

    public function test_password_rejects_more_than_72_utf8_bytes(): void
    {
        $user = $this->createUser([]);
        $this->createMobileToken($user, 'unicode-password');
        $password = str_repeat('é', 40);

        $this->withToken('unicode-password')->putJson('/api/v1/profile/password', [
            'current_password' => 'secret123',
            'password' => $password,
            'password_confirmation' => $password,
        ])->assertUnprocessable()->assertJsonValidationErrors('password');

        $this->assertTrue(Hash::check('secret123', $user->fresh()->password));
    }

    public function test_removing_photo_deletes_only_the_authenticated_users_photo(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('petugas/own.jpg', 'photo');
        Storage::disk('public')->put('petugas/other.jpg', 'other photo');
        $user = $this->createUser(['foto' => 'petugas/own.jpg']);
        $other = $this->createUser([
            'username' => 'other', 'email' => 'other@example.test', 'foto' => 'petugas/other.jpg',
        ]);
        $this->createMobileToken($user, 'remove-photo');

        $this->withToken('remove-photo')->putJson('/api/v1/profile', [
            'nama_lengkap' => $user->nama_lengkap,
            'username' => $user->username,
            'email' => $user->email,
            'hapus_foto' => true,
        ])->assertOk()->assertJsonPath('data.user.foto_url', null);

        $this->assertNull($user->fresh()->foto);
        $this->assertSame('petugas/other.jpg', $other->fresh()->foto);
        Storage::disk('public')->assertMissing('petugas/own.jpg');
        Storage::disk('public')->assertExists('petugas/other.jpg');
    }

    public function test_upload_and_removal_cannot_be_requested_together(): void
    {
        Storage::fake('public');
        $user = $this->createUser([]);
        $this->createMobileToken($user, 'conflicting-photo');

        $this->withToken('conflicting-photo')->post('/api/v1/profile', [
            '_method' => 'PUT',
            'nama_lengkap' => $user->nama_lengkap,
            'username' => $user->username,
            'email' => $user->email,
            'hapus_foto' => '1',
            'foto' => UploadedFile::fake()->image('new.jpg'),
        ], ['Accept' => 'application/json'])->assertUnprocessable()->assertJsonValidationErrors('foto');

        $this->assertNull($user->fresh()->foto);
        $this->assertCount(0, Storage::disk('public')->allFiles());
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
