<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class WargaSeeder extends Seeder
{
    public function run(): void
    {
        $wargaAccounts = [
            [
                'nama_lengkap' => 'Rama Warga',
                'username'     => 'RamaWarga',
                'email'        => 'ramawarga@gmail.com',
                'password'     => 'RamaWarga',
                'role'         => 'warga',
            ],
        ];

        foreach ($wargaAccounts as $account) {
            $data = [
                'nama_lengkap' => $account['nama_lengkap'],
                'username'     => $account['username'],
                'email'        => $account['email'],
                'password'     => Hash::make($account['password']),
                'role'         => $account['role'],
                'is_active'    => true,
            ];

            if (Schema::hasColumn('users', 'name')) {
                $data['name'] = $account['nama_lengkap'];
            }

            if (Schema::hasColumn('users', 'roles')) {
                $data['roles'] = json_encode([$account['role']]);
            }

            DB::table('users')->updateOrInsert(
                ['username' => $account['username']],
                $data
            );
        }
    }
}