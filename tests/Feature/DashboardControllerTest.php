<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\InspeksiIkl;
use App\Models\JadwalInspeksi;
use App\Models\LaporanWarga;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_renders_filters_metrics_and_empty_states(): void
    {
        $admin = $this->createUser('admin');

        $response = $this->actingAs($admin)
            ->withSession(['role' => 'admin'])
            ->get(route('dashboard.admin'));

        $response->assertOk()
            ->assertSeeText('Halo, Administrator Puskesmas')
            ->assertSeeText('Filter Dashboard')
            ->assertSeeText('Periode')
            ->assertSeeText('Wilayah Desa')
            ->assertSeeText('Laporan Menunggu Verifikasi')
            ->assertSeeText('Jadwal IKL Hari Ini')
            ->assertSeeText('Air Memenuhi Syarat')
            ->assertSeeText('Total Intervensi Lapangan')
            ->assertSeeText('Belum ada data laporan')
            ->assertSeeText('Belum ada data kategori')
            ->assertSeeText('Semua laporan warga telah diverifikasi.')
            ->assertSeeText('Belum ada kunjungan hari ini')
            ->assertSee('value="this_month" selected', false)
            ->assertSee('value="last_3_months"', false)
            ->assertSee('images/maskot_berangberang.png', false)
            ->assertViewHas('filters', fn (array $filters): bool => (
                $filters['period'] === 'this_month' && $filters['id_desa'] === null
            ));
    }

    public function test_kepala_puskesmas_can_view_the_dashboard(): void
    {
        $head = $this->createUser('kepala_puskesmas');

        $this->actingAs($head)
            ->withSession(['role' => 'kepala_puskesmas'])
            ->get(route('dashboard.admin'))
            ->assertOk()
            ->assertSeeText('Halo, Kepala Puskesmas');
    }

    public function test_dashboard_applies_period_and_village_filters_to_report_analytics(): void
    {
        $this->travelTo(Carbon::parse('2026-11-24 10:00:00'));
        $admin = $this->createUser('admin');
        $village = Desa::create(['nama_desa' => 'Desa Sasaran']);
        $otherVillage = Desa::create(['nama_desa' => 'Desa Lain']);

        $this->createReport($village, 'IKL-DASH-001', 'menunggu', 'air_masalah', '2026-11-10');
        $this->createReport($village, 'IKL-DASH-002', 'diproses', 'air_sumur_keruh_berbau', '2026-09-10');
        $this->createReport($village, 'IKL-DASH-003', 'selesai', 'jamban_limbah_stbm', '2026-10-10');
        $this->createReport($village, 'IKL-DASH-004', 'menunggu', 'tumpukan_sampah', '2026-01-10');
        $this->createReport($otherVillage, 'IKL-DASH-005', 'menunggu', 'sarang_nyamuk_dbd', '2026-11-11');

        $response = $this->actingAs($admin)
            ->withSession(['role' => 'admin'])
            ->get(route('dashboard.admin', [
                'period' => 'last_3_months',
                'id_desa' => $village->id_desa,
            ]));

        $response->assertOk()
            ->assertViewHas('dashboardMetrics', fn (array $metrics): bool => (
                $metrics['pendingReports'] === 2
            ))
            ->assertViewHas('monthlyChartData', fn (array $chartData): bool => (
                array_sum($chartData['incoming']) === 3
                && array_sum($chartData['resolved']) === 1
            ))
            ->assertViewHas('categoryDistribution', fn (array $categories): bool => (
                $categories[0]['count'] === 2
                && $categories[2]['count'] === 1
                && array_sum(array_column($categories, 'count')) === 3
            ))
            ->assertViewHas('filters', fn (array $filters): bool => (
                $filters['period'] === 'last_3_months'
                && $filters['id_desa'] === $village->id_desa
            ));
    }

    public function test_all_period_includes_older_village_reports_but_monthly_chart_stays_recent(): void
    {
        $this->travelTo(Carbon::parse('2026-11-24 10:00:00'));
        $admin = $this->createUser('admin');
        $village = Desa::create(['nama_desa' => 'Desa Periode Semua']);
        $otherVillage = Desa::create(['nama_desa' => 'Desa Periode Lain']);

        $this->createReport($village, 'IKL-DASH-ALL-01', 'menunggu', 'tumpukan_sampah', '2025-01-10');
        $this->createReport($otherVillage, 'IKL-DASH-ALL-02', 'menunggu', 'air_masalah', '2026-11-10');

        $response = $this->actingAs($admin)
            ->withSession(['role' => 'admin'])
            ->get(route('dashboard.admin', [
                'period' => 'all',
                'id_desa' => $village->id_desa,
            ]));

        $response->assertOk()
            ->assertViewHas('dashboardMetrics', fn (array $metrics): bool => (
                $metrics['pendingReports'] === 1
            ))
            ->assertViewHas('monthlyReportTotal', 0)
            ->assertViewHas('categoryReportTotal', 1);
    }

    public function test_dashboard_filters_today_agenda_and_inspection_metrics_by_village(): void
    {
        $this->travelTo(Carbon::parse('2026-11-24 10:00:00'));
        $admin = $this->createUser('admin');
        $operator = $this->createUser('petugas');
        $village = Desa::create(['nama_desa' => 'Desa Agenda']);
        $otherVillage = Desa::create(['nama_desa' => 'Desa Agenda Lain']);
        $pendingReport = $this->createReport($village, 'IKL-DASH-AGENDA-01', 'menunggu', 'air_masalah', '2026-11-10');
        $completedReport = $this->createReport($village, 'IKL-DASH-AGENDA-02', 'selesai', 'air_masalah', '2026-11-11');
        $otherReport = $this->createReport($otherVillage, 'IKL-DASH-AGENDA-03', 'menunggu', 'air_masalah', '2026-11-12');
        $scheduledVisit = $this->createSchedule($pendingReport, $operator, 'terjadwal');
        $completedVisit = $this->createSchedule($completedReport, $operator, 'selesai');
        $otherVisit = $this->createSchedule($otherReport, $operator, 'terjadwal');

        $this->createInspection($scheduledVisit, 'aman', 'Distribusi Kaporit');
        $this->createInspection($completedVisit, 'risiko_tinggi', '');
        $this->createInspection($otherVisit, 'risiko_sedang', 'KIE');

        $response = $this->actingAs($admin)
            ->withSession(['role' => 'admin'])
            ->get(route('dashboard.admin', [
                'period' => 'this_month',
                'id_desa' => $village->id_desa,
            ]));

        $response->assertOk()
            ->assertSeeText('Desa Agenda')
            ->assertViewHas('dashboardMetrics', [
                'pendingReports' => 1,
                'todaySchedules' => 2,
                'scheduledToday' => 1,
                'completedToday' => 1,
                'inspectionTotal' => 2,
                'inspectionMsCount' => 1,
                'inspectionTmsCount' => 1,
                'msPercentage' => 50,
                'interventions' => 1,
            ])
            ->assertViewHas('todaySchedules', fn ($schedules): bool => (
                $schedules->count() === 2
                && ! $schedules->contains('id_jadwal', $otherVisit->id_jadwal)
            ));
    }

    public function test_dashboard_agenda_paginates_five_schedules_and_preserves_filters(): void
    {
        $this->travelTo(Carbon::parse('2026-11-24 10:00:00'));
        $admin = $this->createUser('admin');
        $operator = $this->createUser('petugas');
        $village = Desa::create(['nama_desa' => 'Desa Agenda Pagination']);
        $visits = [];

        for ($number = 1; $number <= 6; $number++) {
            $report = $this->createReport(
                $village,
                sprintf('IKL-DASH-PAGE-%02d', $number),
                'menunggu',
                'air_masalah',
                '2026-11-20'
            );
            $visits[] = $this->createSchedule($report, $operator, 'terjadwal');
        }

        $firstPage = $this->actingAs($admin)
            ->withSession(['role' => 'admin'])
            ->get(route('dashboard.admin', [
                'period' => 'all',
                'id_desa' => $village->id_desa,
            ]));

        $firstPage->assertSee('aria-label="Pagination"', false)
            ->assertSee('aria-label="Halaman 2"', false)
            ->assertViewHas('todaySchedules', function ($schedules) use ($village, $visits): bool {
                parse_str((string) parse_url($schedules->url(2), PHP_URL_QUERY), $query);

                return $schedules->perPage() === 5
                    && $schedules->total() === 6
                    && $schedules->count() === 5
                    && $schedules->first()->id_jadwal === $visits[0]->id_jadwal
                    && ($query['page_agenda'] ?? null) === '2'
                    && ($query['period'] ?? null) === 'all'
                    && ($query['id_desa'] ?? null) === (string) $village->id_desa;
            });

        $secondPage = $this->actingAs($admin)
            ->withSession(['role' => 'admin'])
            ->get(route('dashboard.admin', [
                'period' => 'all',
                'id_desa' => $village->id_desa,
                'page_agenda' => 2,
            ]));

        $secondPage->assertSee('aria-current="page"', false)
            ->assertViewHas('todaySchedules', fn ($schedules): bool => (
                $schedules->total() === 6
                && $schedules->count() === 1
                && $schedules->first()->id_jadwal === $visits[5]->id_jadwal
            ));
    }

    public function test_dashboard_rejects_invalid_period_and_village_filters(): void
    {
        $admin = $this->createUser('admin');

        $response = $this->actingAs($admin)
            ->withSession(['role' => 'admin'])
            ->get(route('dashboard.admin', [
                'period' => 'fortnight',
                'id_desa' => 999,
            ]));

        $response->assertSessionHasErrors(['period', 'id_desa']);
    }

    private function createUser(string $role): User
    {
        return User::create([
            'nama_lengkap' => match ($role) {
                'admin' => 'Administrator Puskesmas',
                'kepala_puskesmas' => 'Kepala Puskesmas',
                default => 'Petugas Dashboard',
            },
            'username' => $role.'-'.uniqid(),
            'password' => 'password123',
            'role' => $role,
            'is_active' => true,
        ]);
    }

    private function createReport(
        Desa $village,
        string $ticketCode,
        string $status,
        string $category,
        string $createdAt
    ): LaporanWarga {
        $report = LaporanWarga::create([
            'kode_tiket' => $ticketCode,
            'nama_pelapor' => 'Pelapor '.$ticketCode,
            'id_desa' => $village->id_desa,
            'rt' => '01',
            'rw' => '02',
            'kategori_laporan' => $category,
            'deskripsi' => 'Laporan untuk pengujian dashboard.',
            'status_laporan' => $status,
        ]);
        $report->created_at = Carbon::parse($createdAt);
        $report->save();

        return $report;
    }

    private function createSchedule(
        LaporanWarga $report,
        User $operator,
        string $status
    ): JadwalInspeksi {
        return JadwalInspeksi::create([
            'id_laporan' => $report->id_laporan,
            'id_operator' => $operator->id_user,
            'tanggal_kunjungan' => today()->toDateString(),
            'jam_mulai' => '09:30',
            'jenis_kunjungan' => 'ikl_laporan_warga',
            'status_kunjungan' => $status,
        ]);
    }

    private function createInspection(
        JadwalInspeksi $schedule,
        string $riskCategory,
        string $intervention
    ): InspeksiIkl {
        return InspeksiIkl::create([
            'id_jadwal' => $schedule->id_jadwal,
            'jenis_sarana_air' => 'sumur_terlindung',
            'jarak_sumber_pencemar' => 10,
            'kategori_risiko' => $riskCategory,
            'rekomendasi_sanitarian' => 'Pertahankan kondisi sarana.',
            'tanggal_inspeksi' => today()->toDateString(),
            'intervensi_fisik' => $intervention,
        ]);
    }
}
