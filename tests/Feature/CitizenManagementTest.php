<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\MobileAccessToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CitizenManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_citizen_accounts_without_staff_accounts(): void
    {
        $admin = $this->createUser('admin', 'admin');
        $desa = Desa::forceCreate(['nama_desa' => 'Sumbersari']);
        $warga = $this->createUser('warga', 'warga-sumbersari', [
            'nama_lengkap' => 'Warga Sumbersari',
            'id_desa' => $desa->id_desa,
        ]);
        $petugas = $this->createUser('petugas', 'petugas-sumbersari', [
            'nama_lengkap' => 'Petugas Sumbersari',
        ]);
        $adminWithWargaRole = $this->createUser('admin', 'admin-warga', [
            'role' => 'admin,warga',
            'roles' => ['admin', 'warga'],
        ]);

        $response = $this->actingAs($admin)->get(route('warga.index'));

        $response->assertOk()
            ->assertSee('Manajemen Warga')
            ->assertSee($warga->nama_lengkap)
            ->assertSee($warga->username)
            ->assertSee('Sumbersari')
            ->assertDontSee($petugas->username)
            ->assertDontSee($adminWithWargaRole->username);
    }

    public function test_admin_can_search_citizen_accounts(): void
    {
        $admin = $this->createUser('admin', 'admin');
        $matchingWarga = $this->createUser('warga', 'warga-ajung', [
            'nama_lengkap' => 'Warga Ajung',
        ]);
        $otherWarga = $this->createUser('warga', 'warga-kebonsari', [
            'nama_lengkap' => 'Warga Kebonsari',
        ]);

        $response = $this->actingAs($admin)->get(route('warga.index', ['q' => 'Ajung']));

        $response->assertOk()
            ->assertSee($matchingWarga->username)
            ->assertDontSee($otherWarga->username);
    }

    public function test_admin_can_filter_citizen_accounts_by_status(): void
    {
        $admin = $this->createUser('admin', 'admin');
        $activeWarga = $this->createUser('warga', 'warga-active');
        $inactiveWarga = $this->createUser('warga', 'warga-inactive', [
            'is_active' => false,
        ]);

        $activeResponse = $this->actingAs($admin)->get(route('warga.index', ['status' => 'active']));
        $inactiveResponse = $this->actingAs($admin)->get(route('warga.index', ['status' => 'inactive']));

        $activeResponse->assertOk()
            ->assertSee($activeWarga->username)
            ->assertDontSee($inactiveWarga->username);
        $inactiveResponse->assertOk()
            ->assertSee($inactiveWarga->username)
            ->assertDontSee($activeWarga->username);
    }

    public function test_admin_can_view_citizen_details_without_edit_or_delete_actions(): void
    {
        $admin = $this->createUser('admin', 'admin');
        $warga = $this->createUser('warga', 'warga-detail', [
            'nama_lengkap' => 'Warga Detail',
            'alamat' => 'Jl. Sehat No. 1',
        ]);

        $indexResponse = $this->actingAs($admin)->get(route('warga.index'));
        $showResponse = $this->get(route('warga.show', $warga));

        $indexResponse->assertSee(route('warga.show', $warga), false)
            ->assertSee(route('warga.reset-password', $warga), false)
            ->assertDontSee('Edit akun warga')
            ->assertDontSee('Hapus akun warga')
            ->assertDontSee('name="_method" value="DELETE"', false)
            ->assertDontSee('/warga/'.$warga->id_user.'/edit', false);
        $showResponse->assertOk()
            ->assertSee('Warga Detail')
            ->assertSee('Jl. Sehat No. 1')
            ->assertDontSee('Ubah')
            ->assertDontSee('/warga/'.$warga->id_user.'/edit', false);
    }

    public function test_admin_cannot_update_a_citizen_profile_using_the_removed_endpoint(): void
    {
        $admin = $this->createUser('admin', 'admin');
        $desa = Desa::forceCreate(['nama_desa' => 'Ajung']);
        $warga = $this->createUser('warga', 'warga-lama');
        $originalAttributes = $warga->fresh()->getAttributes();

        $response = $this->actingAs($admin)->put('/warga/'.$warga->id_user, [
            'nama_lengkap' => 'Warga Baru',
            'username' => 'warga-baru',
            'email' => 'warga@example.test',
            'no_telepon' => '081234567890',
            'alamat' => 'Jl. Sehat No. 2',
            'id_desa' => $desa->id_desa,
            'is_active' => '0',
            'role' => 'admin',
            'roles' => ['admin'],
        ]);

        $response->assertMethodNotAllowed();
        $this->assertSame($originalAttributes, $warga->fresh()->getAttributes());
    }

    public function test_removed_citizen_edit_page_returns_not_found(): void
    {
        $admin = $this->createUser('admin', 'admin');
        $warga = $this->createUser('warga', 'warga-edit');

        $response = $this->actingAs($admin)->get('/warga/'.$warga->id_user.'/edit');

        $response->assertNotFound();
    }

    public function test_admin_can_reset_a_citizen_password(): void
    {
        $admin = $this->createUser('admin', 'admin');
        $warga = $this->createUser('warga', 'warga-reset');
        $mobileToken = MobileAccessToken::create([
            'id_user' => $warga->id_user,
            'name' => 'flutter-mobile',
            'token_hash' => hash('sha256', 'warga-token'),
        ]);

        $response = $this->actingAs($admin)->post(route('warga.reset-password', $warga));

        $response->assertRedirect(route('warga.index'))
            ->assertSessionHas('success');
        $this->assertTrue(Hash::check('password123', $warga->fresh()->password));
        $this->assertDatabaseMissing('app_mobile_tokens', ['id' => $mobileToken->id]);
    }

    public function test_admin_cannot_delete_a_citizen_account_using_the_removed_endpoint(): void
    {
        $admin = $this->createUser('admin', 'admin');
        $warga = $this->createUser('warga', 'warga-hapus');

        $response = $this->actingAs($admin)->delete('/warga/'.$warga->id_user);

        $response->assertMethodNotAllowed();
        $this->assertModelExists($warga);
    }

    public function test_citizen_details_and_edit_form_are_not_available_through_staff_management(): void
    {
        $admin = $this->createUser('admin', 'admin');
        $warga = $this->createUser('warga', 'warga-edit');

        $showResponse = $this->actingAs($admin)->get(route('users.show', $warga));
        $editResponse = $this->get(route('users.edit', $warga));

        $showResponse->assertNotFound();
        $editResponse->assertNotFound();
    }

    public function test_admin_cannot_update_a_citizen_account_through_staff_management(): void
    {
        $admin = $this->createUser('admin', 'admin');
        $warga = $this->createUser('warga', 'warga-lama');
        $originalAttributes = $warga->fresh()->getAttributes();

        $response = $this->actingAs($admin)->put(route('users.update', $warga), [
            'nama_lengkap' => 'Warga Baru',
            'username' => 'warga-baru',
            'email' => 'warga@example.test',
            'password' => null,
            'no_telepon' => '081234567890',
            'roles' => ['admin'],
            'alamat' => 'Jl. Sehat No. 2',
            'is_active' => false,
        ]);

        $response->assertNotFound();
        $this->assertSame($originalAttributes, $warga->fresh()->getAttributes());
    }

    public function test_admin_cannot_delete_a_citizen_account_through_staff_management(): void
    {
        $admin = $this->createUser('admin', 'admin');
        $warga = $this->createUser('warga', 'warga-hapus');

        $response = $this->actingAs($admin)->delete(route('users.destroy', $warga));

        $response->assertNotFound();
        $this->assertModelExists($warga);
    }

    #[DataProvider('staffRoles')]
    public function test_admin_can_still_view_and_edit_staff_accounts(string $role): void
    {
        $admin = $this->createUser('admin', 'admin');
        $staffAccount = $this->createUser($role, $role.'-edit');

        $showResponse = $this->actingAs($admin)->get(route('users.show', $staffAccount));
        $editResponse = $this->get(route('users.edit', $staffAccount));

        $showResponse->assertOk();
        $editResponse->assertSee($staffAccount->username)
            ->assertSee(route('users.update', $staffAccount), false);
    }

    #[DataProvider('staffRoles')]
    public function test_admin_can_still_delete_a_staff_account(string $role): void
    {
        $admin = $this->createUser('admin', 'admin');
        $staffAccount = $this->createUser($role, $role.'-hapus');

        $response = $this->actingAs($admin)->delete(route('users.destroy', $staffAccount));

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseMissing('app_users', ['id_user' => $staffAccount->id_user]);
    }

    public function test_citizen_actions_return_not_found_for_non_citizen_accounts(): void
    {
        $admin = $this->createUser('admin', 'admin');
        $petugas = $this->createUser('petugas', 'petugas-tidak-boleh');

        $this->actingAs($admin);

        $this->get(route('warga.show', $petugas))->assertNotFound();
        $this->post(route('warga.reset-password', $petugas))->assertNotFound();
        $adminWithWargaRole = $this->createUser('admin', 'admin-warga', [
            'role' => 'admin,warga',
            'roles' => ['admin', 'warga'],
        ]);
        $this->get(route('warga.show', $adminWithWargaRole))->assertNotFound();
        $this->post(route('warga.reset-password', $adminWithWargaRole))->assertNotFound();
        $this->assertModelExists($petugas);
        $this->assertModelExists($adminWithWargaRole);
    }

    public function test_non_admin_cannot_reset_a_citizen_password(): void
    {
        $warga = $this->createUser('warga', 'warga-akses');
        $targetWarga = $this->createUser('warga', 'warga-target');
        $originalPassword = $targetWarga->password;

        $this->actingAs($warga)
            ->post(route('warga.reset-password', $targetWarga))
            ->assertForbidden();

        $this->assertSame($originalPassword, $targetWarga->fresh()->password);
    }

    public function test_non_admin_cannot_view_citizen_accounts(): void
    {
        $warga = $this->createUser('warga', 'warga-test');

        $this->actingAs($warga)->get(route('warga.index'))->assertForbidden();
    }

    public function test_guest_is_redirected_to_login_from_citizen_management(): void
    {
        $this->get(route('warga.index'))->assertRedirect(route('login'));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function staffRoles(): array
    {
        return [
            'admin' => ['admin'],
            'petugas' => ['petugas'],
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createUser(string $role, string $username, array $attributes = []): User
    {
        return User::factory()->create([
            'nama_lengkap' => ucfirst($role),
            'username' => $username,
            'password' => 'secret123',
            'role' => $role,
            'roles' => [$role],
            'is_active' => true,
            ...$attributes,
        ]);
    }
}
