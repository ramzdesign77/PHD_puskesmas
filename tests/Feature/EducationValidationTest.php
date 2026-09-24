<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EducationValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_title_rejects_special_characters(): void
    {
        $response = $this->withSession(['role' => 'admin'])
            ->from('/admin/edukasi/create')
            ->post('/admin/edukasi', [
                'title' => '@#$%^&*(!&(#^E!)',
                'category' => 'Sanitasi',
                'content' => 'Ini adalah konten edukasi sanitasi yang valid untuk pengujian form agar minimal 50 karakter panjangnya.',
            ]);

        $response->assertSessionHasErrors('title');
    }
}
