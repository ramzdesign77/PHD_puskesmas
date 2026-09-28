<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DesaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('desa')->insert([
            ['nama_desa' => 'Sumbersari',   'kode_pos' => '68121', 'created_at' => now()],
            ['nama_desa' => 'Kebonsari',    'kode_pos' => '68122', 'created_at' => now()],
            ['nama_desa' => 'Antirogo',     'kode_pos' => '68121', 'created_at' => now()],
            ['nama_desa' => 'Karangrejo',   'kode_pos' => '68124', 'created_at' => now()],
            ['nama_desa' => 'Tegal Gede',   'kode_pos' => '68121', 'created_at' => now()],
        ]);
    }
}
