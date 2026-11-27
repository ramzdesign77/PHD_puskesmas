<?php

namespace Tests\Feature;

use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_is_repeatable_and_creates_linked_login_accounts(): void
    {
        $this->seed();
        $this->seed();

        $this->assertDatabaseCount('ref_desa', 5);
        $this->assertFalse(Schema::hasTable('app_petugas'));
        $this->assertDatabaseCount('app_users', 2);

        foreach (['admin', 'petugas1'] as $username) {
            $user = DB::table('app_users')->where('username', $username)->first();

            $this->assertNotNull($user);
            $this->assertMatchesRegularExpression('/^USR-[A-Z]{3}-[A-Z0-9]{5}$/', $user->id_user);
        }

        $this->assertDatabaseHas('app_users', [
            'username' => 'petugas1',
            'id_petugas' => 'P1111PK',
            'jabatan' => 'Sanitarian',
        ]);
    }

    public function test_user_seeder_can_be_run_directly_and_is_repeatable(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(UserSeeder::class);

        $this->assertDatabaseCount('app_users', 2);
        $this->assertDatabaseHas('app_users', [
            'username' => 'admin',
            'role' => 'admin',
        ]);
        $this->assertDatabaseHas('app_users', [
            'username' => 'petugas1',
            'role' => 'petugas',
        ]);
        $this->assertTrue(Hash::check(
            'admin123',
            DB::table('app_users')->where('username', 'admin')->value('password'),
        ));
        $this->assertTrue(Hash::check(
            'petugas123',
            DB::table('app_users')->where('username', 'petugas1')->value('password'),
        ));
    }
}
