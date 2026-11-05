<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_renders_hero_mascot_and_summary_cards(): void
    {
        $admin = User::create([
            'nama_lengkap' => 'Administrator Puskesmas',
            'username' => 'administrator',
            'email' => 'admin@example.test',
            'password' => 'secret123',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->withSession([
                'role' => 'admin',
                'user_name' => 'Administrator Puskesmas',
            ])
            ->get(route('dashboard.admin'));

        $response->assertOk()
            ->assertSeeText('Selamat Datang,')
            ->assertSeeText('Total Laporan')
            ->assertSeeText('Menunggu')
            ->assertSeeText('Selesai')
            ->assertSeeText('Aktivitas Terbaru')
            ->assertSeeText('Aksi Cepat')
            ->assertSee('images/maskot_berangberang.png');
    }
}
