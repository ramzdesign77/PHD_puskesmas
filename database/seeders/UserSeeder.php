<?php

namespace Database\Seeders;

use App\Models\User;
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
        $accounts = [
            [
                'nama_lengkap' => 'Administrator',
                'name' => 'Administrator',
                'username' => 'admin',
                'email' => 'admin@klinik-sanitasi.local',
                'password' => 'admin123',
                'role' => 'admin',
                'jabatan' => null,
            ],
            [
                'nama_lengkap' => 'Petugas Kesling',
                'name' => 'Petugas Kesling',
                'username' => 'petugas1',
                'email' => 'petugas@klinik-sanitasi.local',
                'password' => 'petugas123',
                'role' => 'petugas',
                'jabatan' => 'Sanitarian',
            ],
        ];

        foreach ($accounts as $account) {
            DB::transaction(function () use ($account): void {
                $attributes = [
                    'jabatan' => $account['jabatan'],
                    'nama_lengkap' => $account['nama_lengkap'],
                    'name' => $account['name'],
                    'username' => $account['username'],
                    'email' => $account['email'],
                    'password' => Hash::make($account['password']),
                    'role' => $account['role'],
                    'is_active' => true,
                    'updated_at' => now(),
                ];

                User::query()->updateOrCreate(
                    ['username' => $account['username']],
                    $attributes,
                );
            });
        }
    }
}
