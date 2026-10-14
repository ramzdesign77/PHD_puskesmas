<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\Education;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_education_and_village_endpoints_return_mobile_data(): void
    {
        Education::query()->create([
            'title' => 'Air Bersih untuk Keluarga',
            'category' => 'Air & Sanitasi',
            'excerpt' => 'Cara menjaga air bersih di rumah.',
            'content' => 'Simpan air minum pada wadah tertutup dan bersihkan wadah secara berkala.',
        ]);
        Desa::query()->forceCreate(['nama_desa' => 'Sumbersari']);

        $articles = $this->getJson('/api/edukasi');
        $villages = $this->getJson('/api/desa');

        $articles->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.title', 'Air Bersih untuk Keluarga')
            ->assertJsonPath('data.0.category', 'Air & Sanitasi');
        $villages->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.nama_desa', 'Sumbersari');
    }

    public function test_public_api_supports_cross_origin_browser_requests(): void
    {
        $response = $this->withHeaders([
            'Origin' => 'http://localhost:5000',
            'Access-Control-Request-Method' => 'GET',
            'Access-Control-Request-Headers' => 'content-type',
        ])->options('/api/edukasi');

        $response->assertNoContent()
            ->assertHeader('Access-Control-Allow-Origin', '*')
            ->assertHeader('Access-Control-Allow-Methods');
    }

    public function test_mobile_report_can_be_submitted_and_tracked_by_ticket(): void
    {
        $village = Desa::query()->forceCreate(['nama_desa' => 'Sumbersari']);

        $submission = $this->postJson('/api/laporan', [
            'nama_pelapor' => 'Warga Sumbersari',
            'nik_pelapor' => '3509010101010001',
            'no_wa' => '081234567890',
            'id_desa' => $village->id_desa,
            'rt' => '02',
            'rw' => '03',
            'kategori_laporan' => 'air_masalah',
            'deskripsi' => 'Air sumur berubah warna sejak beberapa hari terakhir.',
            'latitude' => -8.1741,
            'longitude' => 113.7208,
        ]);

        $submission->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['kode_tiket']);

        $ticketCode = $submission->json('kode_tiket');
        $this->assertDatabaseHas('laporan_warga', [
            'kode_tiket' => $ticketCode,
            'id_desa' => $village->id_desa,
        ]);

        $this->getJson('/api/laporan/'.$ticketCode)
            ->assertOk()
            ->assertJsonPath('data.kode_tiket', $ticketCode)
            ->assertJsonPath('data.status_laporan', 'menunggu');
    }
}
