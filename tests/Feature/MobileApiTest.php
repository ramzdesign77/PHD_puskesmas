<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\Education;
use App\Models\InspeksiIkl;
use App\Models\JadwalInspeksi;
use App\Models\LaporanWarga;
use App\Models\MobileAccessToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_registration_saves_and_returns_phone_number(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'nama_lengkap' => 'Warga Contoh',
            'username' => 'warga-contoh',
            'email' => 'warga-contoh@example.test',
            'no_telepon' => '081234567890',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.no_telepon', '081234567890');

        $this->assertDatabaseHas('app_users', [
            'username' => 'warga-contoh',
            'name' => 'warga-contoh',
            'no_telepon' => '081234567890',
        ]);
    }

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

    public function test_mobile_report_accepts_new_category_gps_and_address_detail(): void
    {
        $village = Desa::query()->forceCreate(['nama_desa' => 'Sumbersari']);

        $response = $this->postJson('/api/laporan', [
            'nama_pelapor' => 'Warga Sumbersari',
            'no_wa' => '081234567890',
            'id_desa' => $village->id_desa,
            'rt' => '02',
            'rw' => '03',
            'kategori_laporan' => 'sarang_nyamuk_dbd',
            'alamat_detail' => 'Dekat Balai RW 03',
            'latitude' => -8.1741,
            'longitude' => 113.7208,
            'deskripsi' => 'Ditemukan banyak sarang nyamuk di halaman rumah.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.kategori_laporan', 'sarang_nyamuk_dbd')
            ->assertJsonPath('data.kategori_label', 'Sarang Nyamuk / Indikasi DBD')
            ->assertJsonPath('data.latitude', '-8.17410000')
            ->assertJsonPath('data.longitude', '113.72080000');

        $this->assertDatabaseHas('trx_laporan_warga', [
            'kode_tiket' => $response->json('kode_tiket'),
            'kategori_laporan' => 'sarang_nyamuk_dbd',
            'alamat_detail' => 'Dekat Balai RW 03',
            'patokan_alamat' => 'Dekat Balai RW 03',
        ]);
    }

    public function test_authenticated_mobile_report_uses_account_identity(): void
    {
        $village = Desa::query()->forceCreate(['nama_desa' => 'Sumbersari']);
        $user = User::query()->create([
            'nama_lengkap' => 'Warga Login',
            'username' => 'warga-login',
            'email' => 'warga-login@example.test',
            'password' => 'secret123',
            'no_telepon' => '081299999999',
            'role' => 'warga',
            'is_active' => true,
        ]);
        $plainToken = 'warga-mobile-token-'.$user->id_user;
        MobileAccessToken::query()->create([
            'id_user' => $user->id_user,
            'name' => 'flutter-mobile',
            'token_hash' => hash('sha256', $plainToken),
        ]);

        $response = $this->withToken($plainToken)->postJson('/api/laporan', [
            'id_desa' => $village->id_desa,
            'rt' => '04',
            'rw' => '05',
            'kategori_laporan' => 'tumpukan_sampah',
            'patokan_alamat' => 'Sebelah warung Pak Budi',
            'deskripsi' => 'Tumpukan sampah mengganggu akses jalan gang.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.kategori_label', 'Tumpukan Sampah');

        $this->assertDatabaseHas('trx_laporan_warga', [
            'kode_tiket' => $response->json('kode_tiket'),
            'id_user' => $user->id_user,
            'nama_pelapor' => 'Warga Login',
            'no_wa' => '081299999999',
            'alamat_detail' => 'Sebelah warung Pak Budi',
        ]);
    }

    public function test_report_history_only_returns_the_authenticated_users_reports(): void
    {
        $village = Desa::query()->forceCreate(['nama_desa' => 'Sumbersari']);
        $warga = $this->createAuthenticatedWarga('warga-history');
        $otherWarga = $this->createAuthenticatedWarga('warga-history-other');
        $ownReport = $this->createHistoryReport(
            $village,
            $warga['user'],
            'IKL-HISTORY-OWN',
            'menunggu',
            '2026-11-20 10:00:00',
        );
        $this->createHistoryReport(
            $village,
            $otherWarga['user'],
            'IKL-HISTORY-OTHER',
            'menunggu',
            '2026-11-21 10:00:00',
        );

        $this->withToken($warga['token'])
            ->getJson('/api/v1/laporan/history?user_id='.$otherWarga['user']->getKey().'&tickets[]=IKL-HISTORY-OTHER')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.kode_tiket', $ownReport->kode_tiket)
            ->assertJsonPath('pagination.total', 1);
    }

    public function test_report_history_requires_a_valid_mobile_token(): void
    {
        $this->getJson('/api/v1/laporan/history')
            ->assertUnauthorized()
            ->assertJsonPath('success', false);

        $this->getJson('/api/laporan/history')
            ->assertUnauthorized()
            ->assertJsonPath('success', false);
    }

    public function test_report_history_rejects_an_expired_mobile_token(): void
    {
        $warga = $this->createAuthenticatedWarga('warga-history-expired');
        MobileAccessToken::query()
            ->where('token_hash', hash('sha256', $warga['token']))
            ->update(['expires_at' => now()->subMinute()]);

        $this->withToken($warga['token'])
            ->getJson('/api/v1/laporan/history')
            ->assertUnauthorized()
            ->assertJsonPath('success', false);
    }

    public function test_report_history_paginates_ten_reports_without_duplicates(): void
    {
        $village = Desa::query()->forceCreate(['nama_desa' => 'Sumbersari']);
        $warga = $this->createAuthenticatedWarga('warga-history-pages');

        for ($index = 1; $index <= 20; $index++) {
            $createdAt = now()->subMinutes(21 - $index)->toDateTimeString();
            $this->createHistoryReport(
                $village,
                $warga['user'],
                'IKL-LIMIT-'.$index,
                'menunggu',
                $createdAt,
            );
        }

        $firstPage = $this->withToken($warga['token'])
            ->getJson('/api/v1/laporan/history');
        $secondPage = $this->withToken($warga['token'])
            ->getJson('/api/v1/laporan/history?page=2');

        $firstPage->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('data.0.kode_tiket', 'IKL-LIMIT-20')
            ->assertJsonPath('pagination.current_page', 1)
            ->assertJsonPath('pagination.last_page', 2)
            ->assertJsonPath('pagination.per_page', 10)
            ->assertJsonPath('pagination.total', 20)
            ->assertJsonPath('pagination.has_more_pages', true);
        $secondPage->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('data.0.kode_tiket', 'IKL-LIMIT-10')
            ->assertJsonPath('pagination.current_page', 2)
            ->assertJsonPath('pagination.has_more_pages', false)
            ->assertJsonPath('pagination.next_page_url', null);

        $this->assertSame(
            [],
            array_intersect(
                array_column($firstPage->json('data'), 'kode_tiket'),
                array_column($secondPage->json('data'), 'kode_tiket'),
            ),
        );
        $this->assertStringContainsString('page=2', $firstPage->json('pagination.next_page_url'));
    }

    public function test_report_history_filters_by_status(): void
    {
        $village = Desa::query()->forceCreate(['nama_desa' => 'Sumbersari']);
        $warga = $this->createAuthenticatedWarga('warga-history-status');
        $finishedReport = $this->createHistoryReport(
            $village,
            $warga['user'],
            'IKL-STATUS-FINISHED',
            'selesai',
            '2026-11-20 10:00:00',
        );
        $this->createHistoryReport(
            $village,
            $warga['user'],
            'IKL-STATUS-PENDING',
            'menunggu',
            '2026-11-21 10:00:00',
        );

        $this->withToken($warga['token'])
            ->getJson('/api/v1/laporan/history?status=selesai')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.kode_tiket', $finishedReport->kode_tiket);
    }

    public function test_report_history_accepts_a_custom_page_size(): void
    {
        $village = Desa::query()->forceCreate(['nama_desa' => 'Sumbersari']);
        $warga = $this->createAuthenticatedWarga('warga-history-page-size');

        for ($index = 1; $index <= 5; $index++) {
            $this->createHistoryReport(
                $village,
                $warga['user'],
                'IKL-PER-PAGE-'.$index,
                'menunggu',
                now()->subMinutes($index)->toDateTimeString(),
            );
        }

        $this->withToken($warga['token'])
            ->getJson('/api/v1/laporan/history?per_page=3')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('pagination.per_page', 3)
            ->assertJsonPath('pagination.last_page', 2)
            ->assertJsonPath('pagination.total', 5);

        $this->withToken($warga['token'])
            ->getJson('/api/v1/laporan/history?limit=2')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('pagination.per_page', 2);

        $this->withToken($warga['token'])
            ->getJson('/api/v1/laporan/history?limit=100')
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('pagination.per_page', 30);
    }

    public function test_report_history_filters_by_date_range_and_status_together(): void
    {
        $village = Desa::query()->forceCreate(['nama_desa' => 'Sumbersari']);
        $warga = $this->createAuthenticatedWarga('warga-history-date');
        $matchingReport = $this->createHistoryReport(
            $village,
            $warga['user'],
            'IKL-DATE-MATCH',
            'selesai',
            '2026-11-20 10:00:00',
        );
        $this->createHistoryReport(
            $village,
            $warga['user'],
            'IKL-DATE-BEFORE',
            'selesai',
            '2026-11-19 10:00:00',
        );
        $this->createHistoryReport(
            $village,
            $warga['user'],
            'IKL-DATE-WRONG-STATUS',
            'menunggu',
            '2026-11-20 12:00:00',
        );
        $this->createHistoryReport(
            $village,
            $warga['user'],
            'IKL-DATE-AFTER',
            'selesai',
            '2026-11-22 10:00:00',
        );

        $this->withToken($warga['token'])
            ->getJson('/api/v1/laporan/history?status=selesai&start_date=2026-11-20&end_date=2026-11-21')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.kode_tiket', $matchingReport->kode_tiket);
    }

    public function test_report_history_rejects_invalid_date_format_and_range(): void
    {
        $warga = $this->createAuthenticatedWarga('warga-history-invalid-date');

        $this->withToken($warga['token'])
            ->getJson('/api/v1/laporan/history?start_date=2026-02-30')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['start_date']);

        $this->withToken($warga['token'])
            ->getJson('/api/v1/laporan/history?start_date=2026-11-21&end_date=2026-11-20')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['end_date']);
    }

    public function test_report_history_rejects_unknown_status_and_excessive_page_size(): void
    {
        $warga = $this->createAuthenticatedWarga('warga-history-invalid-filter');

        $this->withToken($warga['token'])
            ->getJson('/api/v1/laporan/history?status=not-a-status')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);

        $this->withToken($warga['token'])
            ->getJson('/api/v1/laporan/history?per_page=51')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_report_history_returns_an_empty_page_with_pagination_metadata(): void
    {
        $warga = $this->createAuthenticatedWarga('warga-history-empty');

        $this->withToken($warga['token'])
            ->getJson('/api/v1/laporan/history?page=2')
            ->assertExactJson([
                'success' => true,
                'data' => [],
                'pagination' => [
                    'current_page' => 2,
                    'last_page' => 1,
                    'per_page' => 10,
                    'total' => 0,
                    'next_page_url' => null,
                    'has_more_pages' => false,
                ],
            ]);
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
            ->assertJsonPath('data.status_laporan', 'menunggu')
            ->assertJsonPath('data.kode_tiket', $ticketCode)
            ->assertJsonPath('data.status_laporan', 'menunggu')
            ->assertJsonPath('data.hasil_inspeksi', null);
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

    /**
     * @return array{user: User, token: string}
     */
    private function createAuthenticatedWarga(string $username): array
    {
        $user = User::query()->create([
            'nama_lengkap' => str_replace('-', ' ', $username),
            'username' => $username,
            'email' => $username.'@example.test',
            'password' => 'secret123',
            'role' => 'warga',
            'is_active' => true,
        ]);
        $token = 'mobile-history-token-'.$username;

        MobileAccessToken::query()->create([
            'id_user' => $user->getKey(),
            'name' => 'flutter-mobile',
            'token_hash' => hash('sha256', $token),
        ]);

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    private function createHistoryReport(
        Desa $village,
        ?User $warga,
        string $ticketCode,
        string $status,
        string $createdAt,
    ): LaporanWarga {
        return LaporanWarga::query()->forceCreate([
            'kode_tiket' => $ticketCode,
            'id_user' => $warga?->getKey(),
            'nama_pelapor' => $warga?->nama_lengkap ?? 'Warga Tanpa Akun',
            'id_desa' => $village->getKey(),
            'rt' => '01',
            'rw' => '02',
            'kategori_laporan' => 'tumpukan_sampah',
            'deskripsi' => 'Laporan untuk pengujian riwayat warga.',
            'status_laporan' => $status,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }
}
