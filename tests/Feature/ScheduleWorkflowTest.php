<?php

namespace Tests\Feature;

use App\Models\JadwalInspeksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ScheduleWorkflowTest extends TestCase
{
    use RefreshDatabase;

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
        $this->assertDatabaseHas('jadwal_inspeksi', [
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
        $this->assertDatabaseHas('jadwal_inspeksi', [
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
