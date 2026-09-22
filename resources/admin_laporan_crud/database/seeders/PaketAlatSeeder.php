<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaketAlatSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('paket_alat')->insert([
            [
                'nama_paket' => 'Paket Inspeksi Air Bersih',
                'kategori_laporan' => 'Air Bersih',
                'deskripsi_alat' => 'pH meter, TDS meter, botol sampel steril, cool box, sarung tangan',
                'created_at' => now(),
            ],
            [
                'nama_paket' => 'Paket Inspeksi Sanitasi',
                'kategori_laporan' => 'Sanitasi',
                'deskripsi_alat' => 'Checklist IKL, senter, sarung tangan, masker, kamera',
                'created_at' => now(),
            ],
            [
                'nama_paket' => 'Paket Pengelolaan Sampah',
                'kategori_laporan' => 'Sampah',
                'deskripsi_alat' => 'Timbangan portable, sarung tangan, masker, kantong sampel',
                'created_at' => now(),
            ],
            [
                'nama_paket' => 'Paket Pemantauan Jentik Nyamuk',
                'kategori_laporan' => 'Jentik Nyamuk',
                'deskripsi_alat' => 'Senter, pipet, wadah sampel jentik, formulir PJB, abate',
                'created_at' => now(),
            ],
            [
                'nama_paket' => 'Paket Inspeksi Limbah Industri',
                'kategori_laporan' => 'Limbah',
                'deskripsi_alat' => 'Botol sampel limbah, pH meter, sarung tangan tahan kimia, cool box',
                'created_at' => now(),
            ],
            [
                'nama_paket' => 'Paket Edukasi PHBS',
                'kategori_laporan' => 'PHBS',
                'deskripsi_alat' => 'Media edukasi, checklist observasi, kamera',
                'created_at' => now(),
            ],
            [
                'nama_paket' => 'Paket Umum',
                'kategori_laporan' => 'Lainnya',
                'deskripsi_alat' => 'Checklist umum, kamera, alat tulis',
                'created_at' => now(),
            ],
        ]);
    }
}
