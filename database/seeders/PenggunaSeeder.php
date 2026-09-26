<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class PenggunaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        foreach (range(1,10) as $index) {
            DB::table("laporan_warga")->insert([
                "kode_tiket"       => 'REP-' . $faker->unique()->numberBetween(100, 999),
                "nama_pelapor"     => $faker->name,
                "nik_pelapor"      => $faker->numerify('3509############'),
                "no_wa"            => $faker->phoneNumber,
                "id_desa"          => 1, // Sesuaikan dengan id yang ada di tabel 'desa'
                "rt"               => '00' . $faker->numberBetween(1, 9),
                "rw"               => '00' . $faker->numberBetween(1, 9),
                "kategori_laporan" => $faker->randomElement(['air_masalah', 'sanitasi_lingkungan']),
                "deskripsi"        => $faker->sentence(10),
                "status_laporan"   => $faker->randomElement(['menunggu', 'dijadwalkan', 'selesai', 'ditolak']),
                "created_at"       => now(),
            ]);
        }
    }
}
