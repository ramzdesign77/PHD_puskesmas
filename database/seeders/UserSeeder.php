<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $petugas = DB::table('petugas')
            ->where('no_telepon', '081234560001')
            ->first();

        $users = [
            [
                'nama_lengkap' => 'dr. Kepala Puskesmas',
                'username' => 'admin',
                'email' => 'admin@klinik-sanitasi.local',
                'role' => 'admin',
                'no_telepon' => null,
                'id_petugas' => null,
            ],
            [
                'nama_lengkap' => 'Sari Dewi',
                'username' => 'petugas',
                'email' => 'petugas@klinik-sanitasi.local',
                'role' => 'sanitarian',
                'no_telepon' => $petugas?->no_telepon,
                'id_petugas' => $petugas === null ? null : (string) $petugas->id_petugas,
            ],
        ];

        foreach ($users as $data) {
            if (DB::table('users')->where('username', $data['username'])->exists()) {
                continue;
            }

            DB::table('users')->insert([
                ...$data,
                'password' => Hash::make('password'),
                'is_active' => true,
                'created_at' => now(),
            ]);
        }
    }
}
