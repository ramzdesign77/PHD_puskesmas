<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EducationValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_title_rejects_special_characters(): void
    {
        $admin = User::create([
            'nama_lengkap' => 'Administrator',
            'username' => 'admin',
            'password' => 'password123',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->withSession(['role' => 'admin'])
            ->from('/admin/edukasi/create')
            ->post('/admin/edukasi', [
                'title' => '@#$%^&*(!&(#^E!)',
                'category' => 'Sanitasi',
                'content' => 'Ini adalah konten edukasi sanitasi yang valid untuk pengujian form agar minimal 50 karakter panjangnya.',
            ]);

        $response->assertSessionHasErrors('title');
    }
}
