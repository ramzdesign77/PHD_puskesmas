<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminAndPetugasSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_with_email_and_open_admin_routes(): void
    {
        $admin = $this->createUser('admin', 'admin@example.test');

        $response = $this->post(route('login.post'), [
            'username' => 'admin@example.test',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('dashboard.admin'));
        $this->assertAuthenticatedAs($admin);
        $this->get(route('users.index'))->assertOk();
    }

    public function test_petugas_can_login_with_username_but_cannot_open_admin_routes(): void
    {
        $petugas = $this->createUser('petugas', 'petugas@example.test');

        $response = $this->post(route('login.post'), [
            'username' => 'petugas',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('dashboard.petugas'));
        $this->assertAuthenticatedAs($petugas);
        $this->get(route('users.index'))->assertForbidden();
    }

    public function test_guest_is_redirected_to_login_and_logout_invalidates_authentication(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));

        $admin = $this->createUser('admin', 'admin@example.test');
        $this->actingAs($admin)->post(route('logout'))->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_inactive_admin_cannot_logout_and_receives_warning(): void
    {
        $admin = $this->createUser('admin', 'inactive-admin@example.test');
        $admin->update(['is_active' => false]);

        $response = $this->actingAs($admin)
            ->from(route('dashboard.admin'))
            ->post(route('logout'));

        $response->assertRedirect(route('dashboard.admin'));
        $this->assertAuthenticatedAs($admin);
        $this->assertSame('Akun ini sedang dinonaktifkan. Anda tidak dapat keluar.', session('inactive_logout_warning'));
    }

    public function test_account_seeder_creates_hashed_admin_and_petugas_credentials(): void
    {
        $this->seed(AdminAndPetugasSeeder::class);

        $admin = User::where('username', 'admin')->firstOrFail();
        $petugas = User::where('username', 'petugas1')->firstOrFail();

        $this->assertSame('admin', $admin->role);
        $this->assertTrue(Hash::check('admin123', $admin->password));
        $this->assertSame('petugas', $petugas->role);
        $this->assertTrue(Hash::check('petugas123', $petugas->password));
    }

    public function test_account_can_have_admin_and_petugas_roles(): void
    {
        $admin = $this->createUser('admin', 'admin@example.test');
        $this->actingAs($admin)->post(route('users.store'), [
            'nama_lengkap' => 'Akun Ganda',
            'username' => 'akun_ganda',
            'email' => 'akun_ganda@example.test',
            'password' => 'secret123',
            'no_telepon' => '08123456789',
            'roles' => ['admin', 'petugas'],
            'wilayah_kerja' => 'Wilayah Lain',
        ])->assertRedirect(route('users.index'));

        $user = User::where('username', 'akun_ganda')->firstOrFail();
        $this->assertSame('admin,petugas', $user->role);
        $this->assertSame('Sumbersari', $user->wilayah_kerja);
        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->hasRole('petugas'));
        $this->assertTrue($user->isAdmin());
        $this->assertSame(1, preg_match('/\AAP1111AG\z/', $user->id_petugas));

        $response = $this->post(route('login.post'), [
            'username' => 'akun_ganda',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('dashboard.admin'));
        $this->assertAuthenticatedAs($user);
        $this->get(route('users.index'))->assertOk();
    }

    public function test_petugas_account_creation_generates_id_with_first_serial_and_name_initials(): void
    {
        $admin = $this->createUser('admin', 'admin@example.test');

        $response = $this->actingAs($admin)
            ->withSession(['role' => 'admin'])
            ->post(route('users.store'), [
                'nama_lengkap' => 'Muhammad Denny Ramadhan',
                'id_petugas' => 'ID-MANUAL',
                'username' => 'denny',
                'password' => 'secret123',
                'no_telepon' => '08123456789',
                'roles' => ['petugas'],
                'wilayah_kerja' => 'Wilayah Lain',
            ]);

        $response->assertRedirect(route('users.index'));
        $petugas = User::where('username', 'denny')->firstOrFail();

        $this->assertSame('P1111MD', $petugas->id_petugas);
        $this->assertDatabaseHas('users', [
            'id_user' => $petugas->id_user,
            'id_petugas' => $petugas->id_petugas,
        ]);
    }

    public function test_admin_only_account_creation_generates_id_with_admin_prefix(): void
    {
        $admin = $this->createUser('admin', 'admin@example.test');

        $response = $this->actingAs($admin)
            ->withSession(['role' => 'admin'])
            ->post(route('users.store'), [
                'nama_lengkap' => 'Admin Baru',
                'username' => 'admin_baru',
                'password' => 'secret123',
                'no_telepon' => '08123456789',
                'roles' => ['admin'],
                'wilayah_kerja' => 'Sumbersari',
            ]);

        $response->assertRedirect(route('users.index'));
        $user = User::where('username', 'admin_baru')->firstOrFail();

        $this->assertSame('A1111AB', $user->id_petugas);
    }

    public function test_account_creation_moves_to_next_serial_series_after_nine_accounts(): void
    {
        $admin = $this->createUser('admin', 'admin@example.test');

        foreach (range(1, 9) as $serial) {
            User::create([
                'nama_lengkap' => 'Akun '.$serial,
                'id_petugas' => 'P111'.$serial.'A',
                'username' => 'akun-seri-'.$serial,
                'password' => 'secret123',
                'role' => 'petugas',
                'is_active' => true,
            ]);
        }

        $response = $this->actingAs($admin)
            ->withSession(['role' => 'admin'])
            ->post(route('users.store'), [
                'nama_lengkap' => 'Muhammad Denny Ramadhan',
                'username' => 'seri-kedua',
                'password' => 'secret123',
                'no_telepon' => '08123456789',
                'roles' => ['petugas'],
                'wilayah_kerja' => 'Sumbersari',
            ]);

        $response->assertRedirect(route('users.index'));
        $user = User::where('username', 'seri-kedua')->firstOrFail();

        $this->assertSame('P2111MD', $user->id_petugas);
    }

    public function test_account_update_does_not_change_petugas_id_even_if_a_new_id_is_submitted(): void
    {
        $admin = $this->createUser('admin', 'admin@example.test');
        $petugas = User::create([
            'nama_lengkap' => 'Petugas',
            'id_petugas' => 'P1111P',
            'username' => 'petugas-target',
            'password' => 'secret123',
            'no_telepon' => '08123456789',
            'role' => 'petugas',
            'wilayah_kerja' => 'Sumbersari',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->withSession(['role' => 'admin'])
            ->put(route('users.update', $petugas), [
                'nama_lengkap' => 'Nama Diperbarui',
                'id_petugas' => 'A999TAMBAHAN',
                'username' => 'petugas-target',
                'password' => '',
                'no_telepon' => '08123456789',
                'roles' => ['petugas'],
                'wilayah_kerja' => 'Wilayah Lain',
                'alamat' => '',
                'is_active' => true,
            ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'id_user' => $petugas->id_user,
            'nama_lengkap' => 'Nama Diperbarui',
            'id_petugas' => 'P1111P',
            'wilayah_kerja' => 'Sumbersari',
        ]);
    }

    private function createUser(string $role, string $email): User
    {
        return User::create([
            'nama_lengkap' => ucfirst($role),
            'username' => $role,
            'email' => $email,
            'password' => 'secret123',
            'role' => $role,
            'is_active' => true,
        ]);
    }
}
