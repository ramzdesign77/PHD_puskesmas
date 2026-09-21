<?php

namespace App\Http\Controllers;

class UserManagementController extends Controller
{
    public function index()
    {
        abort_unless(session('role') === 'admin', 403);

        $users = [
            ['name' => 'Petugas Sari Dewi', 'username' => 'sari.dewi', 'role' => 'Sanitarian', 'area' => 'Wilayah Barat', 'status' => 'Aktif'],
            ['name' => 'Petugas Andi Pratama', 'username' => 'andi.pratama', 'role' => 'Sanitarian', 'area' => 'Wilayah Timur', 'status' => 'Aktif'],
            ['name' => 'Rina Kurnia', 'username' => 'rina.kurnia', 'role' => 'Staf Backup Kluster 4', 'area' => 'Wilayah Utara', 'status' => 'Aktif'],
            ['name' => 'dr. Kepala Puskesmas', 'username' => 'kepala.puskesmas', 'role' => 'Kepala Puskesmas', 'area' => 'Semua wilayah', 'status' => 'Aktif'],
        ];

        return view('users.index', compact('users'));
    }
}
