<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\InspeksiIkl;
use App\Models\JadwalInspeksi;
use App\Models\LaporanWarga;
use App\Models\MobileAccessToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InspectionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_officer_submission_calculates_risk_and_stores_private_mobile_details(): void
    {
        $this->travelTo(now()->startOfDay());
        Storage::fake('local');
        $officer = $this->createOfficer();
        $token = $this->createToken($officer);
        $report = $this->createReport();
        $schedule = $this->createSchedule($officer, $report, 'terjadwal');
        $payload = $this->validPayload($schedule);
        $payload['p1_dinding_sumur_retak'] = true;
        $payload['p2_penutup_tidak_rapat'] = true;
        $payload['total_skor_ya'] = 0;
        $payload['kategori_risiko'] = 'aman';
        $payload['intervensi_fisik'] = 'Perbaikan lantai';
        $payload['catatan_intervensi'] = 'Catatan untuk tim internal.';
        $payload['catatan_petugas'] = 'Catatan pribadi petugas.';
        $payload['foto_bukti'] = UploadedFile::fake()->image('bukti.jpg')->size(500);

        $response = $this->withToken($token)->post(
            '/api/v1/officer/inspections',
            $payload,
            ['Accept' => 'application/json'],
        );

        $response->assertCreated()
            ->assertJsonPath('data.id_jadwal', $schedule->id_jadwal)
            ->assertJsonPath('data.total_skor_ya', 2)
            ->assertJsonPath('data.kategori_risiko', 'risiko_sedang')
            ->assertJsonPath('data.label_kategori_risiko', 'Sedang')
            ->assertJsonPath('data.tanggal_inspeksi', now()->toDateString())
            ->assertJsonPath('data.intervensi_fisik', 'Perbaikan lantai')
            ->assertJsonPath('data.status_kunjungan', 'selesai')
            ->assertJsonPath('data.status_laporan', 'selesai');

        $inspection = InspeksiIkl::query()->where('id_jadwal', $schedule->id_jadwal)->firstOrFail();
        $this->assertSame(2, $inspection->total_skor_ya);
        $this->assertSame('risiko_sedang', $inspection->kategori_risiko);
        $this->assertSame('Catatan untuk tim internal.', $inspection->catatan_intervensi);
        $this->assertSame('Catatan pribadi petugas.', $inspection->catatan_petugas);
        Storage::disk('local')->assertExists($inspection->foto_bukti);
        $this->assertDatabaseHas('trx_jadwal_inspeksi', [
            'id_jadwal' => $schedule->id_jadwal,
            'status_kunjungan' => 'selesai',
        ]);
        $this->assertDatabaseHas('trx_laporan_warga', [
            'id_laporan' => $report->id_laporan,
            'status_laporan' => 'selesai',
        ]);
        $this->assertArrayNotHasKey('foto_bukti', $response->json('data'));
        $this->assertArrayNotHasKey('catatan_intervensi', $response->json('data'));
        $this->assertArrayNotHasKey('catatan_petugas', $response->json('data'));
    }

    public function test_inspection_requires_every_checklist_answer_and_rejects_future_dates(): void
    {
        $this->travelTo(now()->startOfDay());
        Storage::fake('local');
        $officer = $this->createOfficer();
        $token = $this->createToken($officer);
        $schedule = $this->createSchedule($officer, null, 'terjadwal');
        $payload = $this->validPayload($schedule);
        unset($payload['p3_lantai_becek_retak']);

        $this->withToken($token)
            ->postJson('/api/v1/officer/inspections', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('p3_lantai_becek_retak');

        $payload = $this->validPayload($schedule);
        $payload['tanggal_inspeksi'] = now()->addDay()->toDateString();

        $this->withToken($token)
            ->postJson('/api/v1/officer/inspections', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('tanggal_inspeksi');

        $payload = $this->validPayload($schedule);
        $payload['foto_bukti'] = UploadedFile::fake()->image('terlalu-besar.jpg')->size(5121);

        $this->withToken($token)
            ->post('/api/v1/officer/inspections', $payload, ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('foto_bukti');

        $this->assertDatabaseCount('trx_inspeksi_ikl', 0);
    }

    public function test_inactive_officer_cannot_submit_inspection(): void
    {
        $officer = $this->createOfficer();
        $token = $this->createToken($officer);
        $officer->update(['is_active' => false]);
        $schedule = $this->createSchedule($officer, null, 'terjadwal');

        $this->withToken($token)
            ->postJson('/api/v1/officer/inspections', $this->validPayload($schedule))
            ->assertForbidden();

        $this->assertDatabaseCount('trx_inspeksi_ikl', 0);
    }

    public function test_inspection_returns_conflict_for_inactive_or_already_completed_schedules(): void
    {
        $officer = $this->createOfficer();
        $token = $this->createToken($officer);
        $cancelledSchedule = $this->createSchedule($officer, null, 'batal');
        $completedSchedule = $this->createSchedule($officer, null, 'terjadwal');
        $this->createInspection($completedSchedule);

        $this->withToken($token)
            ->postJson('/api/v1/officer/inspections', $this->validPayload($cancelledSchedule))
            ->assertConflict();

        $this->withToken($token)
            ->postJson('/api/v1/officer/inspections', $this->validPayload($completedSchedule))
            ->assertConflict();

        $this->assertDatabaseCount('trx_inspeksi_ikl', 1);
    }

    public function test_routine_inspection_returns_null_report_status(): void
    {
        $officer = $this->createOfficer();
        $token = $this->createToken($officer);
        $schedule = $this->createSchedule($officer, null, 'terjadwal');

        $this->withToken($token)
            ->postJson('/api/v1/officer/inspections', $this->validPayload($schedule))
            ->assertCreated()
            ->assertJsonPath('data.status_kunjungan', 'selesai')
            ->assertJsonPath('data.status_laporan', null);
    }

    public function test_ticket_status_returns_latest_inspection_without_private_details(): void
    {
        $officer = $this->createOfficer();
        $report = $this->createReport();
        $this->createSchedule($officer, $report, 'batal');
        $latestSchedule = $this->createSchedule($officer, $report, 'selesai');
        $this->createInspection($latestSchedule, [
            'total_skor_ya' => 3,
            'kategori_risiko' => 'risiko_tinggi',
            'catatan_intervensi' => 'Rahasia tim.',
            'catatan_petugas' => 'Catatan internal.',
            'foto_bukti' => 'inspeksi-ikl/rahasia.jpg',
        ]);

        $response = $this->getJson('/api/laporan/'.$report->kode_tiket);

        $response->assertOk()
            ->assertJsonPath('data.hasil_inspeksi.total_skor_ya', 3)
            ->assertJsonPath('data.hasil_inspeksi.kategori_risiko', 'risiko_tinggi')
            ->assertJsonPath('data.hasil_inspeksi.tanggal_inspeksi', now()->toDateString());

        $inspectionSummary = $response->json('data.hasil_inspeksi');
        $this->assertArrayNotHasKey('catatan_intervensi', $inspectionSummary);
        $this->assertArrayNotHasKey('catatan_petugas', $inspectionSummary);
        $this->assertArrayNotHasKey('foto_bukti', $inspectionSummary);
    }

    public function test_ticket_status_returns_null_when_latest_schedule_has_no_inspection_result(): void
    {
        $officer = $this->createOfficer();
        $report = $this->createReport();
        $this->createSchedule($officer, $report, 'terjadwal');

        $this->getJson('/api/laporan/'.$report->kode_tiket)
            ->assertOk()
            ->assertJsonPath('data.hasil_inspeksi', null);
    }

    private function createOfficer(): User
    {
        return User::query()->create([
            'nama_lengkap' => 'Petugas Inspeksi',
            'username' => 'petugas-inspeksi',
            'email' => 'petugas-inspeksi@example.test',
            'password' => 'secret123',
            'role' => 'petugas',
            'is_active' => true,
        ]);
    }

    private function createToken(User $officer): string
    {
        $plainToken = 'inspection-flow-token';
        MobileAccessToken::query()->create([
            'id_user' => $officer->id_user,
            'name' => 'flutter-mobile',
            'token_hash' => hash('sha256', $plainToken),
            'created_at' => now(),
        ]);

        return $plainToken;
    }

    private function createReport(): LaporanWarga
    {
        $village = Desa::query()->create(['nama_desa' => 'Sumbersari']);

        return LaporanWarga::query()->create([
            'kode_tiket' => 'IKL-FLOW-001',
            'nama_pelapor' => 'Warga Sumbersari',
            'id_desa' => $village->id_desa,
            'rt' => '01',
            'rw' => '02',
            'kategori_laporan' => 'air_masalah',
            'deskripsi' => 'Air sumur perlu diperiksa.',
            'status_laporan' => 'dijadwalkan',
        ]);
    }

    private function createSchedule(User $officer, ?LaporanWarga $report, string $status): JadwalInspeksi
    {
        return JadwalInspeksi::query()->create([
            'id_laporan' => $report?->id_laporan,
            'id_operator' => $officer->id_user,
            'tanggal_kunjungan' => now()->toDateString(),
            'jam_mulai' => '09:00',
            'jenis_kunjungan' => $report === null ? 'ikl_rutin_rt' : 'ikl_laporan_warga',
            'status_kunjungan' => $status,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(JadwalInspeksi $schedule): array
    {
        return [
            'id_jadwal' => $schedule->id_jadwal,
            'jenis_sarana_air' => 'sumur_terlindung',
            'jarak_sumber_pencemar' => 12,
            'tanggal_inspeksi' => now()->toDateString(),
            'p1_dinding_sumur_retak' => false,
            'p2_penutup_tidak_rapat' => false,
            'p3_lantai_becek_retak' => false,
            'p4_spal_tersumbat' => false,
            'p5_air_keruh_berbau' => false,
            'rekomendasi_sanitarian' => 'Perbaiki lantai dan dinding sumur.',
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createInspection(JadwalInspeksi $schedule, array $attributes = []): InspeksiIkl
    {
        return InspeksiIkl::query()->create(array_merge([
            'id_jadwal' => $schedule->id_jadwal,
            'jenis_sarana_air' => 'sumur_terlindung',
            'jarak_sumber_pencemar' => 12,
            'total_skor_ya' => 0,
            'kategori_risiko' => 'aman',
            'rekomendasi_sanitarian' => 'Perbaiki lantai dan dinding sumur.',
            'tanggal_inspeksi' => now()->toDateString(),
        ], $attributes));
    }
}
