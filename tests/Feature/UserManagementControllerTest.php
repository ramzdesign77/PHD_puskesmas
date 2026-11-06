<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserManagementControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_accepts_profile_photos_up_to_2048_kilobytes(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create([
            'username' => 'administrator',
            'role' => 'admin',
            'roles' => ['admin'],
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'nama_lengkap' => 'Petugas Baru',
            'username' => 'petugas-baru',
            'password' => 'password123',
            'no_telepon' => '081234567890',
            'roles' => ['petugas'],
            'foto' => UploadedFile::fake()->image('foto.jpg')->size(2048),
        ]);

        $response->assertRedirect(route('users.index'));
        $user = User::query()->where('username', 'petugas-baru')->firstOrFail();
        Storage::disk('public')->assertExists($user->foto);
    }

    public function test_create_rejects_profile_photos_over_2048_kilobytes(): void
    {
        $admin = User::factory()->create([
            'username' => 'administrator',
            'role' => 'admin',
            'roles' => ['admin'],
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->from(route('users.create'))->post(route('users.store'), [
            'nama_lengkap' => 'Petugas Baru',
            'username' => 'petugas-baru',
            'password' => 'password123',
            'no_telepon' => '081234567890',
            'roles' => ['petugas'],
            'foto' => UploadedFile::fake()->image('foto.jpg')->size(2049),
        ]);

        $response->assertRedirect(route('users.create'))
            ->assertSessionHasErrors('foto');
        $this->assertDatabaseMissing('app_users', ['username' => 'petugas-baru']);
    }

    public function test_update_accepts_profile_photos_up_to_2048_kilobytes(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create([
            'username' => 'administrator',
            'role' => 'admin',
            'roles' => ['admin'],
            'is_active' => true,
        ]);
        $user = User::factory()->create([
            'nama_lengkap' => 'Petugas Lama',
            'username' => 'petugas-lama',
            'id_petugas' => 'P0001',
            'role' => 'petugas',
            'roles' => ['petugas'],
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('users.update', $user), [
            'nama_lengkap' => 'Petugas Lama',
            'username' => 'petugas-lama',
            'password' => '',
            'no_telepon' => '081234567890',
            'roles' => ['petugas'],
            'is_active' => true,
            'foto' => UploadedFile::fake()->image('foto.jpg')->size(2048),
        ]);

        $response->assertRedirect(route('users.index'));
        $user->refresh();
        Storage::disk('public')->assertExists($user->foto);
    }
}
