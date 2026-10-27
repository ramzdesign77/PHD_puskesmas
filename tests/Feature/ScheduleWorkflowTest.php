<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\JadwalInspeksi;
use App\Models\LaporanWarga;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ScheduleWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_resolves_the_latest_schedule_after_a_cancelled_schedule_is_replaced(): void
    {
        $village = Desa::create([
            'nama_desa' => 'Schedule Test Village',
        ]);
        $report = LaporanWarga::create([
            'kode_tiket' => 'IKL-20261027-0001',
            'nama_pelapor' => 'Resident Test',
            'id_desa' => $village->id_desa,
            'rt' => '01',
            'rw' => '02',
            'kategori_laporan' => 'air_masalah',
            'deskripsi' => 'Test report description.',
            'status_laporan' => 'menunggu',
        ]);

        JadwalInspeksi::create([
            'id_laporan' => $report->id_laporan,
            'tanggal_kunjungan' => Carbon::tomorrow()->toDateString(),
            'jenis_kunjungan' => 'ikl_laporan_warga',
            'status_kunjungan' => 'batal',
        ]);
        $replacementSchedule = JadwalInspeksi::create([
            'id_laporan' => $report->id_laporan,
            'tanggal_kunjungan' => Carbon::tomorrow()->toDateString(),
            'jenis_kunjungan' => 'ikl_laporan_warga',
            'status_kunjungan' => 'terjadwal',
        ]);

        $this->assertSame($replacementSchedule->id_jadwal, $report->fresh()->jadwal->id_jadwal);
    }

    public function test_admin_can_create_a_routine_schedule_for_an_operator(): void
    {
        $admin = $this->createUser('admin');
        $operator = $this->createUser('petugas');
        $date = Carbon::tomorrow()->toDateString();

        $response = $this->actingAs($admin)
            ->withSession(['role' => 'admin'])
            ->post(route('schedules.store'), [
                'id_operator' => $operator->id_user,
                'tanggal_kunjungan' => $date,
                'jam_mulai' => '09:30',
                'jenis_kunjungan' => 'ikl_rutin_rt',
            ]);

        $response->assertRedirect(route('schedules.index'));
        $this->assertDatabaseHas('trx_jadwal_inspeksi', [
            'id_operator' => $operator->id_user,
            'tanggal_kunjungan' => $date.' 00:00:00',
            'jam_mulai' => '09:30',
            'jenis_kunjungan' => 'ikl_rutin_rt',
            'status_kunjungan' => 'terjadwal',
        ]);
    }

    public function test_admin_can_assign_an_existing_schedule_to_an_operator(): void
    {
        $admin = $this->createUser('admin');
        $operator = $this->createUser('petugas');
        $schedule = JadwalInspeksi::create([
            'id_laporan' => null,
            'id_operator' => null,
            'id_paket' => null,
            'tanggal_kunjungan' => Carbon::tomorrow()->toDateString(),
            'jam_mulai' => null,
            'jenis_kunjungan' => 'ikl_rutin_rt',
            'status_kunjungan' => 'terjadwal',
        ]);
        $date = Carbon::tomorrow()->toDateString();

        $response = $this->actingAs($admin)
            ->withSession(['role' => 'admin'])
            ->post(route('schedules.assign', $schedule->id_jadwal), [
                'id_operator' => $operator->id_user,
                'tanggal_kunjungan' => $date,
                'jam_mulai' => '14:00',
            ]);

        $response->assertRedirect(route('schedules.index'));
        $this->assertDatabaseHas('trx_jadwal_inspeksi', [
            'id_jadwal' => $schedule->id_jadwal,
            'id_operator' => $operator->id_user,
            'tanggal_kunjungan' => $date.' 00:00:00',
            'jam_mulai' => '14:00',
        ]);
    }

    private function createUser(string $role): User
    {
        return User::create([
            'nama_lengkap' => ucfirst($role),
            'username' => $role.'-'.uniqid(),
            'password' => 'password123',
            'role' => $role,
            'is_active' => true,
        ]);
    }
}
