<?php

namespace Tests\Feature\Api;

use App\Models\Desa;
use App\Models\InspeksiIkl;
use App\Models\JadwalInspeksi;
use App\Models\LaporanWarga;
use App\Models\MobileAccessToken;
use App\Models\PaketAlat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OfficerApiControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_officer_routes_require_a_valid_mobile_token_on_both_prefixes(): void
    {
        $this->getJson('/api/v1/officer/dashboard')->assertUnauthorized();
        $this->getJson('/api/officer/dashboard')->assertUnauthorized();
    }

    public function test_warga_token_cannot_access_officer_dashboard(): void
    {
        $token = $this->createToken($this->createUser('warga'));

        $this->withToken($token)
            ->getJson('/api/v1/officer/dashboard')
            ->assertForbidden();
    }

    public function test_dashboard_returns_officer_summary_and_recent_reports_and_schedules(): void
    {
        $this->travelTo(now()->startOfDay());

        $officer = $this->createUser('petugas', [
            'nama_lengkap' => 'Petugas Sari',
            'wilayah_kerja' => 'Sumbersari',
        ]);
        $token = $this->createToken($officer);
        $village = Desa::query()->create(['nama_desa' => 'Sumbersari']);
        $pendingReport = $this->createReport($village, 'IKL-NEW-001', 'menunggu');
        $scheduledReport = $this->createReport($village, 'IKL-SCHEDULED-001', 'dijadwalkan');
        $schedule = $this->createSchedule($officer, $scheduledReport, 'terjadwal', now()->toDateString());
        $package = PaketAlat::query()->create([
            'nama_paket' => 'Paket Inspeksi Air',
            'kategori_laporan' => 'air_masalah',
            'deskripsi_alat' => 'Alat inspeksi air.',
        ]);
        DB::table('trx_jadwal_inspeksi')
            ->where('id_jadwal', $schedule->id_jadwal)
            ->update(['id_paket' => $package->id_paket]);
        $this->createSchedule($officer, null, 'selesai', now()->toDateString());

        $this->withToken($token)
            ->getJson('/api/v1/officer/dashboard')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.officer.id', $officer->id_user)
            ->assertJsonPath('data.officer.nama_lengkap', 'Petugas Sari')
            ->assertJsonPath('data.officer.nip', null)
            ->assertJsonPath('data.officer.jabatan', 'Petugas Kesling')
            ->assertJsonPath('data.summary.aduan_baru_count', 1)
            ->assertJsonPath('data.summary.jadwal_ikl_hari_ini_count', 2)
            ->assertJsonPath('data.summary.tugas_aktif_count', 1)
            ->assertJsonPath('data.summary.selesai_count', 1)
            ->assertJsonFragment([
                'type' => 'aduan_baru',
                'id_laporan' => $pendingReport->id_laporan,
                'nama_desa' => 'Sumbersari',
            ])
            ->assertJsonFragment([
                'type' => 'jadwal_ikl',
                'nama_pelapor' => 'Resident '.$scheduledReport->kode_tiket,
                'nama_desa' => 'Sumbersari',
                'paket_alat' => 'Paket Inspeksi Air',
            ]);
    }

    public function test_tasks_filter_and_search_return_only_matching_officer_work(): void
    {
        $this->travelTo(now()->startOfDay());

        $officer = $this->createUser('petugas');
        $anotherOfficer = $this->createUser('petugas', ['username' => 'petugas-lain']);
        $token = $this->createToken($officer);
        $village = Desa::query()->create(['nama_desa' => 'Ajung']);
        $report = $this->createReport($village, 'IKL-SEARCH-001', 'menunggu');
        $assignedReport = $this->createReport($village, 'IKL-ASSIGNED-001', 'dijadwalkan');
        $this->createSchedule($officer, $assignedReport, 'terjadwal', now()->toDateString());
        $otherReport = $this->createReport($village, 'IKL-OTHER-001', 'dijadwalkan');
        $this->createSchedule($anotherOfficer, $otherReport, 'terjadwal', now()->toDateString());

        $this->withToken($token)
            ->getJson('/api/v1/officer/tasks?filter=aduan_baru&search=SEARCH-001')
            ->assertOk()
            ->assertJsonPath('meta.filter', 'aduan_baru')
            ->assertJsonPath('data.0.id_laporan', $report->id_laporan)
            ->assertJsonPath('data.0.kode_tiket', 'IKL-SEARCH-001')
            ->assertJsonCount(1, 'data');

        $this->withToken($token)
            ->getJson('/api/officer/tasks?filter=jadwal_ikl')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id_jadwal', JadwalInspeksi::query()
                ->where('id_laporan', $assignedReport->id_laporan)
                ->value('id_jadwal'));
    }

    public function test_invalid_task_filter_returns_validation_errors(): void
    {
        $token = $this->createToken($this->createUser('petugas'));

        $this->withToken($token)
            ->getJson('/api/v1/officer/tasks?filter=unknown')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('filter');
    }

    public function test_officer_can_approve_or_reject_a_pending_report(): void
    {
        $this->travelTo(now()->startOfDay());

        $officer = $this->createUser('petugas');
        $token = $this->createToken($officer);
        $village = Desa::query()->create(['nama_desa' => 'Antirogo']);
        $approvedReport = $this->createReport($village, 'IKL-APPROVE-001', 'menunggu');
        $rejectedReport = $this->createReport($village, 'IKL-REJECT-001', 'menunggu');

        $this->withToken($token)
            ->postJson('/api/v1/officer/reports/'.$approvedReport->id_laporan.'/verify', [
                'action' => 'approve',
                'tanggal_kunjungan' => now()->addDay()->toDateString(),
                'waktu_kunjungan' => '09:30',
            ])
            ->assertOk()
            ->assertJsonPath('data.status_laporan', 'dijadwalkan')
            ->assertJsonPath('data.jadwal.status_kunjungan', 'terjadwal');

        $this->assertDatabaseHas('trx_laporan_warga', [
            'id_laporan' => $approvedReport->id_laporan,
            'status_laporan' => 'dijadwalkan',
        ]);
        $this->assertDatabaseHas('trx_jadwal_inspeksi', [
            'id_laporan' => $approvedReport->id_laporan,
            'id_operator' => $officer->id_user,
            'tanggal_kunjungan' => now()->addDay()->toDateString().' 00:00:00',
            'jam_mulai' => '09:30',
            'status_kunjungan' => 'terjadwal',
        ]);

        $this->withToken($token)
            ->postJson('/api/v1/officer/reports/'.$rejectedReport->id_laporan.'/verify', [
                'action' => 'reject',
                'alasan_penolakan' => 'Lokasi berada di luar area layanan.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status_laporan', 'ditolak')
            ->assertJsonPath('data.alasan_penolakan', 'Lokasi berada di luar area layanan.');

        $this->assertDatabaseHas('trx_laporan_warga', [
            'id_laporan' => $rejectedReport->id_laporan,
            'status_laporan' => 'ditolak',
            'alasan_penolakan' => 'Lokasi berada di luar area layanan.',
        ]);
    }

    public function test_rejecting_report_requires_a_reason(): void
    {
        $token = $this->createToken($this->createUser('petugas'));
        $village = Desa::query()->create(['nama_desa' => 'Kebonsari']);
        $report = $this->createReport($village, 'IKL-REASON-001', 'menunggu');

        $this->withToken($token)
            ->postJson('/api/v1/officer/reports/'.$report->id_laporan.'/verify', [
                'action' => 'reject',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('alasan_penolakan');

        $this->assertDatabaseHas('trx_laporan_warga', [
            'id_laporan' => $report->id_laporan,
            'status_laporan' => 'menunggu',
        ]);
    }

    #[DataProvider('riskScores')]
    public function test_inspection_submission_scores_checklist_and_finishes_schedule(
        int $score,
        string $riskCategory,
    ): void {
        $this->travelTo(now()->startOfDay());
        Storage::fake('public');
        $officer = $this->createUser('petugas');
        $token = $this->createToken($officer);
        $village = Desa::query()->create(['nama_desa' => 'Tegal Gede']);
        $report = $this->createReport($village, 'IKL-INSPECT-'.str_pad((string) $score, 3, '0', STR_PAD_LEFT), 'dijadwalkan');
        $schedule = $this->createSchedule($officer, $report, 'terjadwal', now()->toDateString());
        $answers = [
            'p1_dinding_sumur_retak',
            'p2_lantai_sumur_retak',
            'p3_bibir_sumur_tidak_ada',
            'p4_saluran_pembuangan_rusak',
            'p5_air_keruh_berbau',
        ];
        $payload = [
            'id_jadwal' => $schedule->id_jadwal,
            'jenis_sarana_air' => 'sumur_bor',
            'jarak_sumber_pencemar' => 10,
            'rekomendasi_sanitarian' => 'Bersihkan dan rawat sarana air secara berkala.',
            'intervensi_fisik' => 'Pemberian Kaporit',
            'catatan_intervensi' => 'Dosis sesuai standar setempat.',
            'foto_inspeksi' => UploadedFile::fake()->image('inspeksi.jpg'),
        ];

        foreach ($answers as $position => $answer) {
            $payload[$answer] = $position < $score;
        }

        $response = $this->withToken($token)
            ->postJson('/api/v1/officer/inspections', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.total_skor_ya', $score)
            ->assertJsonPath('data.kategori_risiko', $riskCategory)
            ->assertJsonPath('data.status_kunjungan', 'selesai')
            ->assertJsonPath('data.status_laporan', 'selesai');

        $inspection = InspeksiIkl::query()->where('id_jadwal', $schedule->id_jadwal)->firstOrFail();
        $this->assertSame($score, $inspection->total_skor_ya);
        $this->assertSame($riskCategory, $inspection->kategori_risiko);
        $this->assertSame('Pemberian Kaporit', $inspection->intervensi_fisik);
        $this->assertNotNull($inspection->foto_inspeksi);
        Storage::disk('public')->assertExists($inspection->foto_inspeksi);
        $this->assertDatabaseHas('trx_jadwal_inspeksi', [
            'id_jadwal' => $schedule->id_jadwal,
            'status_kunjungan' => 'selesai',
        ]);
        $this->assertDatabaseHas('trx_laporan_warga', [
            'id_laporan' => $report->id_laporan,
            'status_laporan' => 'selesai',
        ]);
    }

    public static function riskScores(): array
    {
        return [
            'zero risks' => [0, 'aman'],
            'one risk' => [1, 'aman'],
            'two risks' => [2, 'risiko_sedang'],
            'three risks' => [3, 'risiko_sedang'],
            'four risks' => [4, 'risiko_tinggi'],
            'five risks' => [5, 'risiko_tinggi'],
        ];
    }

    public function test_inspection_cannot_be_submitted_for_another_officers_schedule(): void
    {
        $this->travelTo(now()->startOfDay());

        $officer = $this->createUser('petugas');
        $otherOfficer = $this->createUser('petugas', ['username' => 'petugas-lain']);
        $token = $this->createToken($officer);
        $schedule = $this->createSchedule($otherOfficer, null, 'terjadwal', now()->toDateString());

        $this->withToken($token)
            ->postJson('/api/v1/officer/inspections', [
                'id_jadwal' => $schedule->id_jadwal,
                'jenis_sarana_air' => 'sumur_bor',
                'jarak_sumber_pencemar' => 10,
                'p1_dinding_sumur_retak' => false,
                'p2_lantai_sumur_retak' => false,
                'p3_bibir_sumur_tidak_ada' => false,
                'p4_saluran_pembuangan_rusak' => false,
                'p5_air_keruh_berbau' => false,
                'rekomendasi_sanitarian' => 'Periksa kondisi sarana air secara berkala.',
            ])
            ->assertNotFound();

        $this->assertDatabaseCount('trx_inspeksi_ikl', 0);
    }

    public function test_stbm_village_summary_aggregates_reports_and_risk_categories(): void
    {
        $this->travelTo(now()->startOfDay());

        $officer = $this->createUser('petugas');
        $token = $this->createToken($officer);
        $village = Desa::query()->create(['nama_desa' => 'Sumbersari']);
        $report = $this->createReport($village, 'IKL-STBM-001', 'selesai');
        $schedule = $this->createSchedule($officer, $report, 'selesai', now()->toDateString());
        InspeksiIkl::query()->create([
            'id_jadwal' => $schedule->id_jadwal,
            'jenis_sarana_air' => 'sumur_bor',
            'jarak_sumber_pencemar' => 5,
            'total_skor_ya' => 1,
            'kategori_risiko' => 'aman',
            'rekomendasi_sanitarian' => 'Pertahankan kebersihan sarana air.',
            'tanggal_inspeksi' => now()->toDateString(),
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/officer/stbm-desa')
            ->assertOk()
            ->assertJsonPath('data.0.id_desa', $village->id_desa)
            ->assertJsonPath('data.0.nama_desa', 'Sumbersari')
            ->assertJsonPath('data.0.total_aduan', 1)
            ->assertJsonPath('data.0.jumlah_sumur_diperiksa', 1)
            ->assertJsonPath('data.0.risiko_aman', 1)
            ->assertJsonPath('data.0.risiko_sedang', 0)
            ->assertJsonPath('data.0.risiko_tinggi', 0);
    }

    public function test_mobile_login_includes_officer_profile_fields(): void
    {
        $officer = $this->createUser('petugas', [
            'email' => 'mobile-officer@example.test',
            'username' => 'mobile-officer',
            'id_petugas' => '1',
            'wilayah_kerja' => 'Ajung',
        ]);
        DB::table('app_petugas')->insert([
            'id_petugas' => 1,
            'nama_petugas' => 'Petugas Profile',
            'jabatan' => 'Sanitarian',
            'no_telepon' => '081234567801',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->postJson('/api/auth/login', [
            'identifier' => $officer->username,
            'password' => 'secret123',
        ])
            ->assertOk()
            ->assertJsonPath('user.role', 'petugas')
            ->assertJsonPath('user.jabatan', 'Sanitarian')
            ->assertJsonPath('user.nip', null)
            ->assertJsonPath('user.wilayah_kerja', 'Ajung');
    }

    private function createUser(string $role, array $attributes = []): User
    {
        static $sequence = 0;
        $sequence++;

        return User::query()->create(array_merge([
            'nama_lengkap' => ucfirst($role).' Test',
            'username' => $role.'-'.$sequence,
            'email' => $role.'-'.$sequence.'@example.test',
            'password' => 'secret123',
            'role' => $role,
            'is_active' => true,
        ], $attributes));
    }

    private function createToken(User $user): string
    {
        $token = 'officer-token-'.$user->id_user;
        MobileAccessToken::query()->create([
            'id_user' => $user->id_user,
            'name' => 'flutter-mobile',
            'token_hash' => hash('sha256', $token),
            'created_at' => now(),
        ]);

        return $token;
    }

    private function createReport(Desa $village, string $ticket, string $status): LaporanWarga
    {
        return LaporanWarga::query()->create([
            'kode_tiket' => $ticket,
            'nama_pelapor' => 'Resident '.$ticket,
            'id_desa' => $village->id_desa,
            'rt' => '01',
            'rw' => '02',
            'kategori_laporan' => 'air_masalah',
            'deskripsi' => 'Air sumur perlu diperiksa.',
            'status_laporan' => $status,
        ]);
    }

    private function createSchedule(
        User $officer,
        ?LaporanWarga $report,
        string $status,
        string $date,
    ): JadwalInspeksi {
        return JadwalInspeksi::query()->create([
            'id_laporan' => $report?->id_laporan,
            'id_operator' => $officer->id_user,
            'tanggal_kunjungan' => $date,
            'jam_mulai' => '09:00',
            'jenis_kunjungan' => $report === null ? 'ikl_rutin_rt' : 'ikl_laporan_warga',
            'status_kunjungan' => $status,
        ]);
    }
}
