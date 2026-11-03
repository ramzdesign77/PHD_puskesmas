<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class WargaDummySeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create('id_ID');
        $password = Hash::make('warga123'); // di-hash sekali saja supaya cepat

        // ambil id desa yang ada (nama kolom primary key-nya id_desa atau id)
        $desaIds = DB::table('ref_desa')->get()
            ->map(fn ($d) => $d->id_desa ?? $d->id)->all();

        for ($i = 1; $i <= 50; $i++) {
            $nama = $faker->name();

            $id = DB::table('app_users')->insertGetId([
                'nama_lengkap' => $nama,
                'name'         => $nama,
                'username'     => 'wargadummy' . $i,
                'email'        => 'wargadummy' . $i . '@example.com',
                'password'     => $password,
                'no_telepon'   => '08' . $faker->numerify('##########'),
                'role'         => 'warga',
                'alamat'       => $faker->address(),
                'id_desa'      => $desaIds ? $faker->randomElement($desaIds) : null,
                'is_active'    => $faker->boolean(85) ? 1 : 0,
                'created_at'   => $faker->dateTimeBetween('-6 months', 'now'),
                'updated_at'   => now(),
            ]);

            // opsional: kalau id_user di tabelmu memang mengikuti id
            DB::table('app_users')->where('id', $id)->update(['id_user' => $id]);
        }
    }
}
