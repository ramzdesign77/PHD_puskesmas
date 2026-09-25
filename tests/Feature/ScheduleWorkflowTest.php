<?php

namespace Tests\Feature;

use App\Models\JadwalInspeksi;
use App\Models\LaporanWarga;
use App\Models\Petugas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ScheduleWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_citizen_request_creates_report_and_schedule(): void
    {
        $villageId = $this->insertVillage();
        $preferred = Carbon::tomorrow()->setTime(9, 30)->format('Y-m-d\TH:i');

        $response = $this->withSession([
            'role' => 'citizen',
            'user_name' => 'Budi Santoso',
        ])->post(route('schedules.store'), [
            'complaint' => 'Air sumur warga berubah warna dan berbau.',
            'location' => 'Jl. Mawar No. 5',
            'id_desa' => $villageId,
            'rt' => '03',
            'rw' => '02',
            'preferred' => $preferred,
            'notes' => 'Mohon membawa alat uji air.',
        ]);

        $response->assertRedirect(route('schedules.index'));
        $this->assertDatabaseHas('laporan_warga', [
            'nama_pelapor' => 'Budi Santoso',
            'id_desa' => $villageId,
            'status_laporan' => 'menunggu',
        ]);
        $this->assertDatabaseHas('jadwal_inspeksi', [
            'tanggal_kunjungan' => Carbon::parse($preferred)->toDateString(),
            'waktu_kunjungan' => '09:30:00',
            'id_operator' => null,
        ]);
    }

    public function test_admin_can_assign_officer_and_update_report_status(): void
    {
        $villageId = $this->insertVillage();
        $report = LaporanWarga::create([
            'kode_tiket' => 'TCK-TEST-001',
            'nama_pelapor' => 'Budi Santoso',
            'nik_pelapor' => null,
            'no_wa' => null,
            'id_desa' => $villageId,
            'rt' => '01',
            'rw' => '02',
            'kategori_laporan' => 'sanitasi_lingkungan',
            'deskripsi' => 'Keluhan saluran air.',
            'status_laporan' => 'menunggu',
        ]);
        $schedule = JadwalInspeksi::create([
            'id_laporan' => $report->getKey(),
            'tanggal_kunjungan' => Carbon::tomorrow()->toDateString(),
            'jenis_kunjungan' => 'ikl_laporan_warga',
            'status_kunjungan' => 'terjadwal',
        ]);
        $officer = Petugas::create([
            'nama_petugas' => 'Petugas Sari Dewi',
            'jabatan' => 'Sanitarian',
            'is_active' => true,
        ]);

        $response = $this->withSession(['role' => 'admin'])
            ->post(route('schedules.assign', $schedule->getKey()), [
                'officer_id' => $officer->getKey(),
                'assigned_date' => Carbon::tomorrow()->toDateString(),
                'assigned_time' => '14:00',
            ]);

        $response->assertRedirect(route('schedules.index'));
        $this->assertDatabaseHas('jadwal_inspeksi', [
            'id_jadwal' => $schedule->getKey(),
            'id_operator' => $officer->getKey(),
            'waktu_kunjungan' => '14:00:00',
        ]);
        $this->assertDatabaseHas('laporan_warga', [
            'id_laporan' => $report->getKey(),
            'status_laporan' => 'dijadwalkan',
        ]);
    }

    private function insertVillage(): int
    {
        return (int) DB::table('desa')->insertGetId([
            'nama_desa' => 'Sumbersari',
            'kode_pos' => '68175',
        ]);
    }
}
