<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\Education;
use App\Models\InspeksiIkl;
use App\Models\JadwalInspeksi;
use App\Models\LaporanWarga;
use App\Models\User;
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
        $this->assertDatabaseHas('trx_laporan_warga', [
            'kode_tiket' => $ticketCode,
            'id_desa' => $village->id_desa,
            'nik_pelapor' => null,
        ]);

        $this->getJson('/api/v1/laporan/track/'.$ticketCode)
            ->assertOk()
            ->assertJsonPath('data.kode_tiket', $ticketCode)
            ->assertJsonPath('data.status_laporan', 'menunggu')
            ->assertJsonPath('data.status_step', 'dikirim')
            ->assertJsonPath('data.nama_desa', 'Sumbersari')
            ->assertJsonPath('data.rt', '02')
            ->assertJsonPath('data.rw', '03')
            ->assertJsonPath('data.kategori_laporan', 'air_masalah')
            ->assertJsonPath('data.jadwal', null)
            ->assertJsonPath('data.hasil_ikl', null);

        $this->getJson('/api/laporan/'.$ticketCode)
            ->assertOk()
            ->assertJsonPath('data.desa', 'Sumbersari')
            ->assertJsonPath('data.status_laporan', 'menunggu');
    }

    public function test_ticket_tracking_returns_schedule_and_inspection_details(): void
    {
        $village = Desa::query()->forceCreate(['nama_desa' => 'Sumbersari']);
        $operator = User::query()->create([
            'nama_lengkap' => 'Petugas Sumbersari',
            'username' => 'petugas-tracking',
            'password' => 'password123',
            'role' => 'petugas',
        ]);
        $laporan = LaporanWarga::query()->create([
            'kode_tiket' => 'IKL-20261029-TRACK',
            'nama_pelapor' => 'Warga Sumbersari',
            'id_desa' => $village->id_desa,
            'rt' => '02',
            'rw' => '03',
            'kategori_laporan' => 'air_masalah',
            'deskripsi' => 'Air sumur berubah warna sejak beberapa hari terakhir.',
            'status_laporan' => 'ditindaklanjuti',
        ]);
        $jadwal = JadwalInspeksi::query()->create([
            'id_laporan' => $laporan->id_laporan,
            'id_operator' => $operator->id_user,
            'tanggal_kunjungan' => '2026-10-30',
            'jam_mulai' => '09:30',
            'jenis_kunjungan' => 'ikl_laporan_warga',
            'status_kunjungan' => 'terjadwal',
        ]);
        InspeksiIkl::query()->create([
            'id_jadwal' => $jadwal->id_jadwal,
            'jenis_sarana_air' => 'sumur_bor',
            'jarak_sumber_pencemar' => 10,
            'total_skor_ya' => 2,
            'kategori_risiko' => 'risiko_sedang',
            'rekomendasi_sanitarian' => 'Perbaiki kondisi sarana air.',
            'tanggal_inspeksi' => '2026-10-30',
            'intervensi_fisik' => 'Pemasangan penutup sumur',
        ]);

        $this->getJson('/api/v1/laporan/track/'.$laporan->kode_tiket)
            ->assertOk()
            ->assertJsonPath('data.status_laporan', 'ditindaklanjuti')
            ->assertJsonPath('data.status_step', 'ditindaklanjuti')
            ->assertJsonPath('data.nama_desa', 'Sumbersari')
            ->assertJsonPath('data.jadwal.tanggal_kunjungan', '2026-10-30')
            ->assertJsonPath('data.jadwal.waktu_kunjungan', '09:30')
            ->assertJsonPath('data.jadwal.status_kunjungan', 'terjadwal')
            ->assertJsonPath('data.jadwal.nama_petugas', 'Petugas Sumbersari')
            ->assertJsonPath('data.hasil_ikl.kategori_risiko', 'risiko_sedang')
            ->assertJsonPath('data.hasil_ikl.total_skor_ya', 2)
            ->assertJsonPath('data.hasil_ikl.rekomendasi_sanitarian', 'Perbaiki kondisi sarana air.')
            ->assertJsonPath('data.hasil_ikl.intervensi_fisik', 'Pemasangan penutup sumur')
            ->assertJsonPath('data.hasil_ikl.tanggal_inspeksi', '2026-10-30');
    }
}
