<?php

namespace Database\Seeders;

use App\Models\Desa;
use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class WargaDummySeeder extends Seeder
{
    public function run(): void
    {
        $faker = Factory::create('id_ID');
        $password = Hash::make('warga123'); // di-hash sekali saja supaya cepat

        $desaIds = Desa::query()->pluck('id_desa')->all();

        for ($i = 1; $i <= 50; $i++) {
            $nama = $faker->name();

            User::query()->create([
                'nama_lengkap' => $nama,
                'name' => $nama,
                'username' => 'wargadummy'.$i,
                'email' => 'wargadummy'.$i.'@example.com',
                'password' => $password,
                'no_telepon' => '08'.$faker->numerify('##########'),
                'role' => 'warga',
                'alamat' => $faker->address(),
                'id_desa' => $desaIds === [] ? null : $faker->randomElement($desaIds),
                'is_active' => $faker->boolean(85) ? 1 : 0,
                'created_at' => $faker->dateTimeBetween('-6 months', 'now'),
                'updated_at' => now(),
            ]);
        }
    }
}
