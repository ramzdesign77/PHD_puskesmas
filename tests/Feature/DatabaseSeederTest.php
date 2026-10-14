<?php

namespace Tests\Feature;

use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_is_repeatable_and_creates_linked_login_accounts(): void
    {
        $this->seed();
        $this->seed();

        $this->assertDatabaseCount('desa', 5);
        $this->assertDatabaseCount('paket_alat', 7);
        $this->assertDatabaseCount('petugas', 5);
        $this->assertDatabaseCount('users', 2);

        foreach (['admin', 'petugas1'] as $username) {
            $user = DB::table('users')->where('username', $username)->first();

            $this->assertNotNull($user);
            $this->assertSame((int) $user->id, (int) $user->id_user);
        }

        $this->assertDatabaseHas('users', [
            'username' => 'petugas1',
            'id_petugas' => (string) DB::table('petugas')
                ->where('no_telepon', '081234560001')
                ->value('id_petugas'),
        ]);
    }

    public function test_user_seeder_can_be_run_directly_and_is_repeatable(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(UserSeeder::class);

        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseHas('users', [
            'username' => 'admin',
            'role' => 'admin',
        ]);
        $this->assertDatabaseHas('users', [
            'username' => 'petugas1',
            'role' => 'petugas',
        ]);
        $this->assertTrue(Hash::check(
            'admin123',
            DB::table('users')->where('username', 'admin')->value('password'),
        ));
        $this->assertTrue(Hash::check(
            'petugas123',
            DB::table('users')->where('username', 'petugas1')->value('password'),
        ));
    }
}
