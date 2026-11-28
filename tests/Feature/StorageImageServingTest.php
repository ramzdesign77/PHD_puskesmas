<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class StorageImageServingTest extends TestCase
{
    public function test_returns_public_storage_file_when_it_exists(): void
    {
        $path = 'test-folder/sample.png';
        $fullPath = storage_path('app/public/'.$path);
        File::ensureDirectoryExists(dirname($fullPath));
        file_put_contents($fullPath, 'sample-image-content');

        try {
            $response = $this->get('/storage/'.$path);

            $response->assertOk()
                ->assertHeader('Cache-Control', 'max-age=86400, public');
        } finally {
            File::delete($fullPath);
            File::deleteDirectory(dirname($fullPath));
        }
    }

    public function test_returns_404_when_public_storage_file_does_not_exist(): void
    {
        $this->get('/storage/test-folder/missing.png')->assertNotFound();
    }

    public function test_forbids_path_traversal_requests(): void
    {
        $this->get('/storage/%2E%2E/.env')->assertForbidden();
    }
}
