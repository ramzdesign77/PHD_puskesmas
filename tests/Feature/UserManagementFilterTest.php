<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserManagementFilterTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('petugasFilters')]
    public function test_petugas_filter_includes_both_role_names_and_multiple_roles(string $filter): void
    {
        $admin = $this->createAccount('admin', 'z-admin');
        $petugas = $this->createAccount('petugas', 'a-petugas');
        $sanitarian = $this->createAccount('sanitarian', 'b-sanitarian');
        $adminPetugas = $this->createAccount('admin,petugas', 'c-admin-petugas');
        $petugasAdmin = $this->createAccount('petugas,admin', 'd-petugas-admin');
        $adminSanitarian = $this->createAccount('admin,sanitarian', 'e-admin-sanitarian');
        $this->createAccount('warga', 'f-warga');
        $this->createAccount('petugas_lama', 'g-other-role');
        $this->createAccount('petugas', 'h-deleted-petugas')->delete();

        $response = $this->actingAs($admin)->get(route('users.index', ['role' => $filter]));

        $response->assertSee('<option value="petugas" selected>Petugas</option>', false);
        $users = $response->viewData('users');
        $this->assertSame([
            $petugas->id_user,
            $sanitarian->id_user,
            $adminPetugas->id_user,
            $petugasAdmin->id_user,
            $adminSanitarian->id_user,
        ], $users->getCollection()->pluck('id_user')->all());
        $this->assertSame(5, $users->total());
    }

    #[DataProvider('petugasFiltersWithStatuses')]
    public function test_petugas_filter_keeps_search_and_status_constraints(string $filter, string $status, bool $isActive): void
    {
        $admin = $this->createAccount('admin', 'match-admin', $isActive);
        $petugas = $this->createAccount('petugas', 'match-petugas', $isActive);
        $sanitarian = $this->createAccount('sanitarian', 'match-sanitarian', $isActive);
        $this->createAccount('petugas', 'other-petugas', $isActive);
        $this->createAccount('sanitarian', 'other-sanitarian', $isActive);
        $this->createAccount('petugas', 'match-petugas-other-status', ! $isActive);
        $this->createAccount('sanitarian', 'match-sanitarian-other-status', ! $isActive);

        $response = $this->actingAs($admin)->get(route('users.index', [
            'role' => $filter,
            'q' => 'match',
            'status' => $status,
        ]));

        $response->assertOk();
        $users = $response->viewData('users');
        $this->assertSame([
            $petugas->id_user,
            $sanitarian->id_user,
        ], $users->getCollection()->pluck('id_user')->all());
        $this->assertSame(2, $users->total());
    }

    public function test_admin_filter_includes_admin_accounts_and_excludes_staff_only_accounts(): void
    {
        $admin = $this->createAccount('admin', 'a-admin');
        $adminPetugas = $this->createAccount('admin,petugas', 'b-admin-petugas');
        $petugasAdmin = $this->createAccount('petugas,admin', 'c-petugas-admin');
        $adminSanitarian = $this->createAccount('admin,sanitarian', 'd-admin-sanitarian');
        $this->createAccount('petugas', 'e-petugas');
        $this->createAccount('sanitarian', 'f-sanitarian');
        $this->createAccount('warga', 'g-warga');

        $response = $this->actingAs($admin)->get(route('users.index', ['role' => 'admin']));

        $response->assertOk();
        $users = $response->viewData('users');
        $this->assertSame([
            $admin->id_user,
            $adminPetugas->id_user,
            $petugasAdmin->id_user,
            $adminSanitarian->id_user,
        ], $users->getCollection()->pluck('id_user')->all());
        $this->assertSame(4, $users->total());
    }

    /**
     * @return array<string, array{string}>
     */
    public static function petugasFilters(): array
    {
        return [
            'petugas' => ['petugas'],
            'legacy sanitarian' => ['sanitarian'],
        ];
    }

    /**
     * @return array<string, array{string, string, bool}>
     */
    public static function petugasFiltersWithStatuses(): array
    {
        return [
            'active petugas' => ['petugas', 'active', true],
            'inactive petugas' => ['petugas', 'inactive', false],
            'active legacy sanitarian' => ['sanitarian', 'active', true],
            'inactive legacy sanitarian' => ['sanitarian', 'inactive', false],
        ];
    }

    private function createAccount(string $role, string $username, bool $isActive = true): User
    {
        return User::factory()->create([
            'nama_lengkap' => $username,
            'username' => $username,
            'role' => $role,
            'is_active' => $isActive,
        ]);
    }
}
