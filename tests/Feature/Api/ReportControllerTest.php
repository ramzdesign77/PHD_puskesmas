<?php

namespace Tests\Feature\Api;

use App\Models\Desa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_report_is_saved_and_returns_created_response(): void
    {
        Storage::fake('public');
        $village = Desa::query()->forceCreate(['nama_desa' => 'Sumbersari']);

        $response = $this->postJson('/api/reports', [
            'nama_lengkap' => 'Warga Sumbersari',
            'nik' => '3509010101010001',
            'nomor_telepon' => '081234567890',
            'village_id' => $village->id_desa,
            'rt' => '02',
            'rw' => '03',
            'kategori_laporan' => 'Masalah air',
            'deskripsi_masalah' => 'Air sumur berubah warna sejak beberapa hari terakhir.',
            'foto_bukti' => UploadedFile::fake()->image('bukti.jpg'),
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Laporan berhasil dikirim!')
            ->assertJsonPath('data.nama_lengkap', 'Warga Sumbersari')
            ->assertJsonPath('data.village_id', $village->id_desa)
            ->assertJsonPath('data.status', 'Pendataan/Pending');
        $this->assertDatabaseHas('trx_laporan_warga', [
            'nama_pelapor' => 'Warga Sumbersari',
            'nik_pelapor' => '3509010101010001',
            'id_desa' => $village->id_desa,
            'status_laporan' => 'menunggu',
        ]);
        Storage::disk('public')->assertExists($response->json('data.foto_bukti'));
    }

    public function test_report_can_be_submitted_without_a_nik(): void
    {
        $village = Desa::query()->forceCreate(['nama_desa' => 'Sumbersari']);

        $this->postJson('/api/reports', [
            'nama_lengkap' => 'Warga Sumbersari',
            'nomor_telepon' => '081234567890',
            'village_id' => $village->id_desa,
            'rt' => '02',
            'rw' => '03',
            'kategori_laporan' => 'Masalah air',
            'deskripsi_masalah' => 'Air sumur berubah warna sejak beberapa hari terakhir.',
        ])
            ->assertCreated()
            ->assertJsonPath('data.nik', null);

        $this->assertDatabaseHas('trx_laporan_warga', [
            'nama_pelapor' => 'Warga Sumbersari',
            'nik_pelapor' => null,
        ]);
    }

    public function test_missing_required_report_fields_return_validation_errors(): void
    {
        $this->postJson('/api/reports', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'nama_lengkap',
                'nomor_telepon',
                'village_id',
                'rt',
                'rw',
                'kategori_laporan',
                'deskripsi_masalah',
            ]);

        $this->assertDatabaseCount('trx_laporan_warga', 0);
    }

    public function test_invalid_report_values_and_photo_return_validation_errors(): void
    {
        $this->postJson('/api/reports', [
            'nama_lengkap' => 'Warga',
            'nik' => str_repeat('1', 17),
            'nomor_telepon' => '081234567890',
            'village_id' => 999999,
            'rt' => '02',
            'rw' => '03',
            'kategori_laporan' => 'Kategori tidak dikenal',
            'deskripsi_masalah' => str_repeat('a', 2001),
            'foto_bukti' => UploadedFile::fake()->create('bukti.txt', 1, 'text/plain'),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'nik',
                'village_id',
                'kategori_laporan',
                'deskripsi_masalah',
                'foto_bukti',
            ]);

        $this->assertDatabaseCount('trx_laporan_warga', 0);
    }
}
