<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminAndPetugasSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'nama_lengkap' => 'Administrator',
                'username' => 'admin',
                'email' => 'admin@puskesmas.go.id',
                'password' => 'admin123',
                'role' => 'admin',
            ],
            [
                'nama_lengkap' => 'Petugas Kesling',
                'username' => 'petugas1',
                'email' => 'petugas@puskesmas.go.id',
                'password' => 'petugas123',
                'role' => 'petugas',
            ],
        ];

        foreach ($accounts as $account) {
            DB::table('users')->updateOrInsert(
                ['username' => $account['username']],
                [
                    'nama_lengkap' => $account['nama_lengkap'],
                    'username' => $account['username'],
                    'email' => $account['email'],
                    'password' => Hash::make($account['password']),
                    'role' => $account['role'],
                    'is_active' => true,
                ]
            );
        }
    }
}
