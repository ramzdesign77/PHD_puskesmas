<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\JadwalInspeksi;
use App\Models\LaporanWarga;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FullSystemSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pages_load_and_petugas_is_denied_from_admin_pages(): void
    {
        $admin = $this->createUser('admin', 'admin-smoke');
        $warga = $this->createUser('warga', 'warga-smoke');
        $adminPages = [
            '/dashboard',
            '/reports',
            '/schedules',
            '/admin/inspeksi-ikl',
            '/users',
            '/admin/edukasi',
        ];

        foreach ($adminPages as $path) {
            $this->actingAs($admin)->withSession(['role' => 'admin'])->get($path)->assertOk();
            if ($path === '/dashboard') {
                $this->actingAs($warga)->withSession(['role' => 'warga'])->get($path)->assertForbidden();

                continue;
            }

            $this->actingAs($warga)->withSession(['role' => 'warga'])->get($path)
                ->assertStatus(403, 'Unauthorized role unexpectedly accessed '.$path);
        }
    }

    public function test_schedule_pagination_accepts_all_supported_page_sizes(): void
    {
        $admin = $this->createUser('admin', 'pagination-admin');
        $operator = $this->createUser('petugas', 'pagination-operator');

        foreach (range(1, 101) as $index) {
            JadwalInspeksi::query()->create([
                'id_operator' => $operator->id_user,
                'tanggal_kunjungan' => now()->addDays($index)->toDateString(),
                'jam_mulai' => '09:00',
                'jenis_kunjungan' => 'ikl_rutin_rt',
                'status_kunjungan' => 'terjadwal',
            ]);
        }

        foreach ([20, 50, 80, 100] as $pageSize) {
            $response = $this->actingAs($admin)->withSession(['role' => 'admin'])
                ->get('/schedules?per_page='.$pageSize)
                ->assertOk()
                ->assertSee('value="'.$pageSize.'" selected', false);
            $this->assertStringContainsString('Menampilkan 1–'.$pageSize.' dari 101 data', $response->getContent());
        }
    }

    public function test_mobile_report_history_officer_login_inspection_and_ticket_tracking(): void
    {
        $this->travelTo(now()->startOfDay());
        $village = Desa::query()->create(['nama_desa' => 'Sumbersari']);
        $warga = $this->createUser('warga', 'smoke-history-warga');
        $login = $this->postJson('/api/v1/login', [
            'login' => $warga->username,
            'password' => 'secret123',
        ])->assertOk();
        $wargaToken = $login->json('data.token');

        $submission = $this->withToken($wargaToken)->postJson('/api/v1/laporan', [
            'nama_pelapor' => 'Warga Smoke',
            'no_wa' => '081234567890',
            'id_desa' => $village->id_desa,
            'rt' => '01',
            'rw' => '02',
            'kategori_laporan' => 'air_masalah',
            'deskripsi' => 'Air sumur berubah warna.',
        ])->assertCreated()->assertJsonStructure(['kode_tiket']);
        $ticket = $submission->json('kode_tiket');

        foreach (range(1, 16) as $index) {
            $localTicket = 'SMOKE-HISTORY-'.$index;
            LaporanWarga::query()->create([
                'kode_tiket' => $localTicket,
                'id_user' => $warga->id_user,
                'nama_pelapor' => 'Warga Smoke',
                'id_desa' => $village->id_desa,
                'rt' => '01',
                'rw' => '02',
                'kategori_laporan' => 'air_masalah',
                'deskripsi' => 'Laporan history smoke test.',
                'status_laporan' => 'menunggu',
            ]);
        }
        $this->withToken($wargaToken)
            ->getJson('/api/v1/laporan/history?limit=10')
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('pagination.per_page', 10)
            ->assertJsonPath('pagination.total', 17);

        $admin = $this->createUser('admin', 'smoke-admin');
        $officer = $this->createUser('petugas', 'smoke-officer');
        $report = LaporanWarga::query()->where('kode_tiket', $ticket)->firstOrFail();
        $this->actingAs($admin)->withSession(['role' => 'admin'])
            ->post('/reports/'.$report->id_laporan.'/jadwalkan', [
                'id_operator' => $officer->id_user,
                'tanggal_kunjungan' => now()->toDateString(),
                'jam_mulai' => '09:00',
                'jenis_kunjungan' => 'ikl_laporan_warga',
            ])->assertRedirect();
        $this->actingAs($admin)->withSession(['role' => 'admin'])
            ->from('/reports')
            ->post('/reports/'.$report->id_laporan.'/jadwalkan', [
                'id_operator' => $officer->id_user,
                'tanggal_kunjungan' => now()->toDateString(),
                'jam_mulai' => '09:00',
                'jenis_kunjungan' => 'ikl_laporan_warga',
            ])->assertSessionHasErrors('jam_mulai');
        $schedule = JadwalInspeksi::query()->where('id_laporan', $report->id_laporan)->firstOrFail();

        $login = $this->postJson('/api/v1/login', [
            'login' => $officer->username,
            'password' => 'secret123',
        ])->assertOk();
        $token = $login->json('data.token');
        $this->withToken($token)->getJson('/api/v1/officer/dashboard')->assertOk();
        $this->withToken($token)->getJson('/api/v1/officer/tasks')->assertOk();
        $this->withToken($token)->postJson('/api/v1/officer/inspections', [
            'id_jadwal' => $schedule->id_jadwal,
            'jenis_sarana_air' => 'sumur_terlindung',
            'jarak_sumber_pencemar' => 12,
            'tanggal_inspeksi' => now()->toDateString(),
            'p1_dinding_sumur_retak' => true,
            'p2_penutup_tidak_rapat' => true,
            'p3_lantai_becek_retak' => false,
            'p4_spal_tersumbat' => false,
            'p5_air_keruh_berbau' => false,
            'p6_indikator_6' => false,
            'p7_indikator_7' => false,
            'rekomendasi_sanitarian' => 'Perbaiki dinding dan penutup sumur.',
        ])->assertCreated()
            ->assertJsonPath('data.total_skor_ya', 2)
            ->assertJsonPath('data.status_laporan', 'selesai');

        $this->getJson('/api/v1/laporan/track/'.$ticket)->assertOk()
            ->assertJsonPath('data.status_laporan', 'selesai')
            ->assertJsonPath('data.hasil_ikl.total_skor_ya', 2)
            ->assertJsonPath('data.hasil_ikl.kategori_risiko', 'risiko_sedang')
            ->assertJsonPath('data.hasil_ikl.rekomendasi_sanitarian', 'Perbaiki dinding dan penutup sumur.');
    }

    private function createUser(string $role, string $username): User
    {
        return User::query()->create([
            'nama_lengkap' => ucfirst($role).' Smoke',
            'username' => $username,
            'email' => $username.'@example.test',
            'password' => 'secret123',
            'role' => $role,
            'is_active' => true,
        ]);
    }
}
