<?php

namespace Tests\Feature;

use App\Models\InspeksiIkl;
use App\Models\JadwalInspeksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_edit_completed_and_cancelled_schedule_history_without_changing_status(): void
    {
        $admin = $this->createUser('admin');
        $operator = $this->createUser('petugas');
        $completedSchedule = $this->createSchedule($operator, 'selesai');
        $cancelledSchedule = $this->createSchedule($operator, 'batal');

        $response = $this->actingAs($admin)
            ->withSession(['role' => 'admin'])
            ->put(route('schedules.history.update', $completedSchedule->id_jadwal), [
                'id_operator' => $operator->id_user,
                'tanggal_kunjungan' => '2026-09-10',
                'jam_mulai' => '09:30',
            ]);

        $response->assertRedirect(route('schedules.index'));
        $this->assertDatabaseHas('jadwal_inspeksi', [
            'id_jadwal' => $completedSchedule->id_jadwal,
            'status_kunjungan' => 'selesai',
            'tanggal_kunjungan' => '2026-09-10 00:00:00',
            'jam_mulai' => '09:30',
        ]);

        $this->put(route('schedules.history.update', $cancelledSchedule->id_jadwal), [
            'id_operator' => $operator->id_user,
            'tanggal_kunjungan' => '2026-09-11',
            'jam_mulai' => '10:00',
        ])->assertRedirect(route('schedules.index'));

        $this->assertDatabaseHas('jadwal_inspeksi', [
            'id_jadwal' => $cancelledSchedule->id_jadwal,
            'status_kunjungan' => 'batal',
            'tanggal_kunjungan' => '2026-09-11 00:00:00',
            'jam_mulai' => '10:00',
        ]);
    }

    public function test_admin_can_delete_cancelled_schedule_without_inspection(): void
    {
        $admin = $this->createUser('admin');
        $operator = $this->createUser('petugas');
        $schedule = $this->createSchedule($operator, 'batal');

        $response = $this->actingAs($admin)
            ->withSession(['role' => 'admin'])
            ->delete(route('schedules.destroy', $schedule->id_jadwal));

        $response->assertRedirect(route('schedules.index'));
        $this->assertDatabaseMissing('jadwal_inspeksi', ['id_jadwal' => $schedule->id_jadwal]);
    }

    public function test_completed_schedule_and_inspection_cannot_be_deleted(): void
    {
        $admin = $this->createUser('admin');
        $operator = $this->createUser('petugas');
        $schedule = $this->createSchedule($operator, 'selesai');
        $inspection = InspeksiIkl::create([
            'id_jadwal' => $schedule->id_jadwal,
            'jenis_sarana_air' => 'sumur_bor',
            'jarak_sumber_pencemar' => 10,
            'total_skor_ya' => 0,
            'kategori_risiko' => 'aman',
            'rekomendasi_sanitarian' => 'Pertahankan kondisi sarana.',
            'tanggal_inspeksi' => '2026-09-10',
        ]);

        $response = $this->actingAs($admin)
            ->withSession(['role' => 'admin'])
            ->delete(route('schedules.destroy', $schedule->id_jadwal));

        $response->assertRedirect(route('schedules.index'))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('jadwal_inspeksi', ['id_jadwal' => $schedule->id_jadwal]);
        $this->assertDatabaseHas('inspeksi_ikl', ['id_inspeksi' => $inspection->id_inspeksi]);
    }

    public function test_petugas_cannot_edit_terminal_schedule_history(): void
    {
        $petugas = $this->createUser('petugas');
        $schedule = $this->createSchedule($petugas, 'batal');

        $this->actingAs($petugas)
            ->withSession(['role' => 'petugas'])
            ->put(route('schedules.history.update', $schedule->id_jadwal), [
                'id_operator' => $petugas->id_user,
                'tanggal_kunjungan' => '2026-09-10',
                'jam_mulai' => '09:30',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('jadwal_inspeksi', [
            'id_jadwal' => $schedule->id_jadwal,
            'status_kunjungan' => 'batal',
            'tanggal_kunjungan' => '2026-09-01 00:00:00',
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

    private function createSchedule(User $operator, string $status): JadwalInspeksi
    {
        return JadwalInspeksi::create([
            'id_laporan' => null,
            'id_operator' => $operator->id_user,
            'id_paket' => null,
            'tanggal_kunjungan' => '2026-09-01',
            'jam_mulai' => '08:00',
            'jenis_kunjungan' => 'ikl_rutin_rt',
            'status_kunjungan' => $status,
        ]);
    }
}
