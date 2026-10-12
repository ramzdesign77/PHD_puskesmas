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
            'wilayah_kerja' => 'Sumbersari',
        ])->assertRedirect(route('users.index'));

        $user = User::where('username', 'akun_ganda')->firstOrFail();
        $this->assertSame('admin,petugas', $user->role);
        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->hasRole('petugas'));
        $this->assertTrue($user->isAdmin());

        $response = $this->post(route('login.post'), [
            'username' => 'akun_ganda',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('dashboard.admin'));
        $this->assertAuthenticatedAs($user);
        $this->get(route('users.index'))->assertOk();
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
