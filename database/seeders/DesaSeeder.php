<?php

namespace Database\Seeders;

use App\Models\Desa;
use Illuminate\Database\Seeder;

class DesaSeeder extends Seeder
{
    public function run(): void
    {
        $desa = [
            ['nama_desa' => 'Sumbersari', 'kode_pos' => '68121'],
            ['nama_desa' => 'Kebonsari', 'kode_pos' => '68122'],
            ['nama_desa' => 'Antirogo', 'kode_pos' => '68121'],
            ['nama_desa' => 'Karangrejo', 'kode_pos' => '68124'],
            ['nama_desa' => 'Tegal Gede', 'kode_pos' => '68121'],
        ];

        foreach ($desa as $data) {
            Desa::query()->updateOrCreate(
                ['nama_desa' => $data['nama_desa']],
                ['kode_pos' => $data['kode_pos']],
            );
        }
    }
}
