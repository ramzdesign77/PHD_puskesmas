<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PetugasSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('petugas')->insert([
            ['nama_petugas' => 'Sari Dewi',        'jabatan' => 'Sanitarian',       'no_telepon' => '081234560001', 'is_active' => 1, 'created_at' => now()],
            ['nama_petugas' => 'Agus Purnomo',     'jabatan' => 'Sanitarian',       'no_telepon' => '081234560002', 'is_active' => 1, 'created_at' => now()],
            ['nama_petugas' => 'Rina Wulandari',   'jabatan' => 'Petugas Lapangan', 'no_telepon' => '081234560003', 'is_active' => 1, 'created_at' => now()],
            ['nama_petugas' => 'Dedi Kurniawan',   'jabatan' => 'Petugas Lapangan', 'no_telepon' => '081234560004', 'is_active' => 1, 'created_at' => now()],
            ['nama_petugas' => 'Maya Anggraini',   'jabatan' => 'Promkes',          'no_telepon' => '081234560005', 'is_active' => 1, 'created_at' => now()],
        ]);
    }
}
