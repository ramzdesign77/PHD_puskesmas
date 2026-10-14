<?php

namespace Database\Seeders;

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
        $this->call(PetugasSeeder::class);

        $petugasId = DB::table('petugas')
            ->where('no_telepon', '081234560001')
            ->value('id_petugas');

        $accounts = [
            [
                'nama_lengkap' => 'Administrator',
                'name' => 'Administrator',
                'username' => 'admin',
                'email' => 'admin@klinik-sanitasi.local',
                'password' => 'admin123',
                'role' => 'admin',
                'id_petugas' => null,
            ],
            [
                'nama_lengkap' => 'Petugas Kesling',
                'name' => 'Petugas Kesling',
                'username' => 'petugas1',
                'email' => 'petugas@klinik-sanitasi.local',
                'password' => 'petugas123',
                'role' => 'petugas',
                'id_petugas' => $petugasId === null ? null : (string) $petugasId,
            ],
        ];

        foreach ($accounts as $account) {
            DB::transaction(function () use ($account): void {
                $user = DB::table('users')
                    ->where('username', $account['username'])
                    ->first(['id']);

                $timestamp = now();
                $attributes = [
                    'id_user' => $user?->id,
                    'id_petugas' => $account['id_petugas'],
                    'nama_lengkap' => $account['nama_lengkap'],
                    'name' => $account['name'],
                    'username' => $account['username'],
                    'email' => $account['email'],
                    'password' => Hash::make($account['password']),
                    'role' => $account['role'],
                    'is_active' => true,
                    'updated_at' => $timestamp,
                ];

                if ($user !== null) {
                    DB::table('users')->where('id', $user->id)->update($attributes);

                    return;
                }

                $attributes['id_user'] = null;
                $attributes['created_at'] = $timestamp;
                $id = DB::table('users')->insertGetId($attributes, 'id');

                DB::table('users')->where('id', $id)->update(['id_user' => $id]);
            });
        }
    }
}
