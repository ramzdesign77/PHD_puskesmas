<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PetugasSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'nama_lengkap' => 'Administrator Puskesmas',
                'id_petugas' => 'ADM-001',
                'username' => 'admin',
                'email' => 'admin@puskesmas.test',
                'password' => 'Admin123!',
                'role' => 'admin',
            ],
            [
                'nama_lengkap' => 'Sari Dewi',
                'id_petugas' => 'SAN-001',
                'username' => 'sanitarian',
                'email' => 'sanitarian@puskesmas.test',
                'password' => 'Sanitasi123!',
                'role' => 'sanitarian',
            ],
        ];

        foreach ($accounts as $account) {
            User::updateOrCreate(
                ['username' => $account['username']],
                [
                    'nama_lengkap' => $account['nama_lengkap'],
                    'id_petugas' => $account['id_petugas'],
                    'email' => $account['email'],
                    'password' => Hash::make($account['password']),
                    'role' => $account['role'],
                    'is_active' => true,
                ]
            );
        }

        $petugas = [
            ['nama_petugas' => 'Sari Dewi', 'jabatan' => 'Sanitarian', 'no_telepon' => '081234560001'],
            ['nama_petugas' => 'Agus Purnomo', 'jabatan' => 'Sanitarian', 'no_telepon' => '081234560002'],
            ['nama_petugas' => 'Rina Wulandari', 'jabatan' => 'Petugas Lapangan', 'no_telepon' => '081234560003'],
            ['nama_petugas' => 'Dedi Kurniawan', 'jabatan' => 'Petugas Lapangan', 'no_telepon' => '081234560004'],
            ['nama_petugas' => 'Maya Anggraini', 'jabatan' => 'Promkes', 'no_telepon' => '081234560005'],
        ];

        foreach ($petugas as $data) {
            DB::table('petugas')->updateOrInsert(
                ['nama_petugas' => $data['nama_petugas']],
                [...$data, 'is_active' => true]
            );
        }
    }
}
