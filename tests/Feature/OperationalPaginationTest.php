<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\InspeksiIkl;
use App\Models\JadwalInspeksi;
use App\Models\LaporanWarga;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_operational_lists_show_one_hundred_rows_when_requested(): void
    {
        $admin = User::query()->create([
            'nama_lengkap' => 'Admin Pagination',
            'username' => 'admin-pagination',
            'email' => 'admin-pagination@example.test',
            'password' => 'secret123',
            'role' => 'admin',
            'is_active' => true,
        ]);
        $operator = User::query()->create([
            'nama_lengkap' => 'Petugas Pagination',
            'username' => 'petugas-pagination',
            'email' => 'petugas-pagination@example.test',
            'password' => 'secret123',
            'role' => 'petugas',
            'is_active' => true,
        ]);
        $village = Desa::query()->create(['nama_desa' => 'Sumbersari']);

        for ($index = 1; $index <= 101; $index++) {
            $this->createReport($village, $index, 'menunggu');

            $completedReport = $this->createReport($village, $index + 1000, 'selesai');
            $schedule = JadwalInspeksi::query()->create([
                'id_laporan' => $completedReport->id_laporan,
                'id_operator' => $operator->id_user,
                'tanggal_kunjungan' => now()->toDateString(),
                'jam_mulai' => '09:00',
                'jenis_kunjungan' => 'ikl_laporan_warga',
                'status_kunjungan' => 'selesai',
            ]);
            InspeksiIkl::query()->create([
                'id_jadwal' => $schedule->id_jadwal,
                'jenis_sarana_air' => 'sumur_terlindung',
                'jarak_sumber_pencemar' => 12,
                'total_skor_ya' => 0,
                'kategori_risiko' => 'aman',
                'rekomendasi_sanitarian' => 'Pertahankan kondisi sarana.',
                'tanggal_inspeksi' => now()->toDateString(),
            ]);
        }

        $this->actingAs($admin)
            ->withSession(['role' => 'admin']);

        foreach ([
            route('schedules.index', ['per_page' => 100]) => 'Menampilkan 1–100 dari 101 data',
            route('reports.index', ['per_page' => 100]) => 'Menampilkan 1–100 dari 101 data',
            route('reports.index', ['per_page' => 100, 'tab' => 'rekap']) => 'Menampilkan 1–100 dari 101 data',
            route('admin.laporan.index', ['per_page' => 100]) => 'Menampilkan 1–100 dari 202 data',
            route('admin.inspeksi-ikl.index', ['per_page' => 100]) => 'Menampilkan 1–100 dari 101 data',
        ] as $url => $range) {
            $this->get($url)
                ->assertOk()
                ->assertSee($range)
                ->assertSee('value="100" selected', false);
        }

        $this->get(route('schedules.index', ['per_page' => 100, 'q' => 'IKL-PAGE-']))
            ->assertOk()
            ->assertSee('Menampilkan 1–100 dari 101 data')
            ->assertSee('per_page=100', false)
            ->assertSee('q=IKL-PAGE-', false);
    }

    private function createReport(Desa $village, int $number, string $status): LaporanWarga
    {
        return LaporanWarga::query()->create([
            'kode_tiket' => 'IKL-PAGE-'.$number,
            'nama_pelapor' => 'Warga Pagination '.$number,
            'id_desa' => $village->id_desa,
            'rt' => '01',
            'rw' => '02',
            'kategori_laporan' => 'air_masalah',
            'deskripsi' => 'Laporan pagination '.$number,
            'status_laporan' => $status,
        ]);
    }
}
