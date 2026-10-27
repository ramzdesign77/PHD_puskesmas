<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PetugasSeeder extends Seeder
{
    public function run(): void
    {
        $petugas = [
            ['nama_petugas' => 'Sari Dewi', 'jabatan' => 'Sanitarian', 'no_telepon' => '081234560001'],
            ['nama_petugas' => 'Agus Purnomo', 'jabatan' => 'Sanitarian', 'no_telepon' => '081234560002'],
            ['nama_petugas' => 'Rina Wulandari', 'jabatan' => 'Petugas Lapangan', 'no_telepon' => '081234560003'],
            ['nama_petugas' => 'Dedi Kurniawan', 'jabatan' => 'Petugas Lapangan', 'no_telepon' => '081234560004'],
            ['nama_petugas' => 'Maya Anggraini', 'jabatan' => 'Promkes', 'no_telepon' => '081234560005'],
        ];

        foreach ($petugas as $data) {
            DB::table('app_petugas')->updateOrInsert(
                ['no_telepon' => $data['no_telepon']],
                [
                    'nama_petugas' => $data['nama_petugas'],
                    'jabatan' => $data['jabatan'],
                    'is_active' => true,
                    'updated_at' => now(),
                ],
            );
        }
    }
}
