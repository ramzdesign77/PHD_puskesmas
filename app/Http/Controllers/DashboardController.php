<?php

namespace App\Http\Controllers;

use App\Models\JadwalInspeksi;
use App\Models\LaporanWarga;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $role = session('role');

        // Mengambil data sesungguhnya dari database
        $stats = [
            'total_reports'   => LaporanWarga::count(),
            'pending'         => LaporanWarga::whereIn('status_laporan', ['menunggu', 'dibaca'])->count(),
            'in_progress'     => LaporanWarga::where('status_laporan', 'dijadwalkan')->count(),
            'resolved'        => LaporanWarga::where('status_laporan', 'selesai')->count(),
            'active_officers' => User::where('is_active', true)->whereIn('role', ['sanitarian', 'staf_backup_kluster4'])->count(),
            'articles'        => 0, // Belum ada tabel Edukasi untuk saat ini
            'schedules'       => JadwalInspeksi::where('status_kunjungan', 'terjadwal')->count(),
        ];

        // Ambil 5 aktivitas laporan terbaru dari database
        $latestReports = LaporanWarga::orderBy('id_laporan', 'desc')->take(5)->get();
        $recent_activities = [];

        foreach ($latestReports as $report) {
            $color = match ($report->status_laporan) {
                'menunggu'    => 'yellow',
                'dijadwalkan' => 'blue',
                'selesai'     => 'green',
                'ditolak'     => 'red',
                default       => 'gray',
            };

            $icon = match ($report->status_laporan) {
                'menunggu'    => 'report',
                'dijadwalkan' => 'calendar',
                'selesai'     => 'check',
                'ditolak'     => 'close',
                default       => 'info',
            };

            $statusLabel = ucfirst($report->status_laporan);

            $recent_activities[] = [
                'icon'  => $icon,
                'text'  => "Laporan #{$report->kode_tiket} - {$statusLabel}",
                'time'  => $report->created_at ? $report->created_at->diffForHumans() : '-',
                'color' => $color,
            ];
        }

        return view('dashboard.index', compact('stats', 'recent_activities', 'role'));
    }
}
