<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserModelCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_user_records_keep_ids_and_names_synchronized(): void
    {
        $user = User::create([
            'nama_lengkap' => 'Administrator Test',
            'username' => 'administrator-test',
            'email' => 'administrator-test@example.test',
            'password' => 'test-password',
            'role' => 'admin',
        ]);

        $storedUser = DB::table('app_users')->where('username', 'administrator-test')->first();

        $this->assertSame($storedUser->id, $storedUser->id_user);
        $this->assertSame('Administrator Test', $storedUser->name);
        $this->assertSame('Administrator Test', $storedUser->nama_lengkap);
        $this->assertSame('admin', $storedUser->role);
        $this->assertSame(['admin'], json_decode($storedUser->roles, true));

        $user->update(['role' => 'admin,petugas']);

        $this->assertSame(['admin', 'petugas'], $user->fresh()->roles());
        $this->assertSame(
            'admin,petugas',
            DB::table('app_users')->where('username', 'administrator-test')->value('role'),
        );
    }

    public function test_roles_array_updates_the_legacy_role_string(): void
    {
        $user = User::create([
            'name' => 'Petugas Test',
            'username' => 'petugas-test',
            'email' => 'petugas-test@example.test',
            'password' => 'test-password',
            'roles' => ['petugas', 'sanitarian'],
        ]);

        $this->assertSame('Petugas Test', $user->nama_lengkap);
        $this->assertSame('petugas,sanitarian', $user->role);
        $this->assertSame(['petugas', 'sanitarian'], $user->roles());
    }
}
