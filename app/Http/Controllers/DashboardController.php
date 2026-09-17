<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        $role = session('role');

        $stats = [
            'total_reports'   => 128,
            'pending'         => 34,
            'in_progress'     => 21,
            'resolved'        => 73,
            'active_officers' => 8,
            'articles'        => 15,
            'schedules'       => 12,
        ];

        $recent_activities = [
            ['icon' => 'report',    'text' => 'Laporan baru dari Jl. Mangga No.12', 'time' => '5 menit lalu',  'color' => 'red'],
            ['icon' => 'check',     'text' => 'Laporan #REP-024 ditandai Selesai',  'time' => '1 jam lalu',    'color' => 'green'],
            ['icon' => 'calendar',  'text' => 'Jadwal kunjungan baru ditambahkan',  'time' => '2 jam lalu',    'color' => 'blue'],
            ['icon' => 'article',   'text' => 'Artikel edukasi baru dipublikasikan','time' => '3 jam lalu',    'color' => 'purple'],
            ['icon' => 'officer',   'text' => 'Petugas Andi ditugaskan ke wilayah Barat', 'time' => '5 jam lalu', 'color' => 'yellow'],
        ];

        return view('dashboard.index', compact('stats', 'recent_activities', 'role'));
    }
}
