<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    private function getMockSchedules(): array
    {
        return [
            [
                'id'         => 'SCH-001',
                'citizen'    => 'Ahmad Fauzi',
                'report_ref' => 'REP-001',
                'complaint'  => 'Sumur tercemar limbah industri',
                'location'   => 'Jl. Mawar No.5, RT 03/02',
                'preferred'  => '2026-09-20 09:00',
                'officer'    => 'Petugas Andi Susilo',
                'assigned_date' => '2026-09-20',
                'assigned_time' => '09:00',
                'status'     => 'assigned',
                'notes'      => 'Bawa alat uji kualitas air',
            ],
            [
                'id'         => 'SCH-002',
                'citizen'    => 'Dewi Lestari',
                'report_ref' => 'REP-004',
                'complaint'  => 'Genangan air sarang nyamuk',
                'location'   => 'Gang Cempaka No.3, RT 04/01',
                'preferred'  => '2026-09-21 10:00',
                'officer'    => null,
                'assigned_date' => null,
                'assigned_time' => null,
                'status'     => 'pending',
                'notes'      => '',
            ],
            [
                'id'         => 'SCH-003',
                'citizen'    => 'Hendra Wijaya',
                'report_ref' => 'REP-007',
                'complaint'  => 'Limbah pabrik ke sungai',
                'location'   => 'Jl. Industri No.45, RT 02/06',
                'preferred'  => '2026-09-22 08:00',
                'officer'    => null,
                'assigned_date' => null,
                'assigned_time' => null,
                'status'     => 'pending',
                'notes'      => '',
            ],
            [
                'id'         => 'SCH-004',
                'citizen'    => 'Rizki Hidayat',
                'report_ref' => 'REP-005',
                'complaint'  => 'Air PDAM berwarna dan berbau',
                'location'   => 'Jl. Kenanga No.20, RT 05/04',
                'preferred'  => '2026-09-23 14:00',
                'officer'    => 'Petugas Sari Dewi',
                'assigned_date' => '2026-09-23',
                'assigned_time' => '14:00',
                'status'     => 'assigned',
                'notes'      => 'Koordinasi dengan PDAM',
            ],
        ];
    }

    private function getMockOfficers(): array
    {
        return [
            ['id' => 1, 'name' => 'Petugas Andi Susilo',   'area' => 'Wilayah Utara'],
            ['id' => 2, 'name' => 'Petugas Sari Dewi',     'area' => 'Wilayah Selatan'],
            ['id' => 3, 'name' => 'Petugas Budi Rahman',   'area' => 'Wilayah Timur'],
            ['id' => 4, 'name' => 'Petugas Nurul Hidayah', 'area' => 'Wilayah Barat'],
        ];
    }

    public function index()
    {
        $schedules = $this->getMockSchedules();
        $officers  = $this->getMockOfficers();
        $role      = session('role');

        // Filter for officer: only show their assigned schedules
        if ($role === 'officer') {
            $officerName = session('user_name');
            $schedules   = array_filter($schedules, fn($s) => $s['officer'] === $officerName);
        }

        return view('schedules.index', compact('schedules', 'officers', 'role'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'report_ref'  => 'required|string',
            'complaint'   => 'required|string|min:5',
            'location'    => 'required|string',
            'preferred'   => 'required|string',
        ]);

        return redirect()->route('schedules.index')
            ->with('success', 'Permintaan kunjungan berhasil dikirim! Admin akan menentukan petugas dan waktu kunjungan.');
    }

    public function assign(Request $request, $id)
    {
        $request->validate([
            'officer'       => 'required|string',
            'assigned_date' => 'required|date',
            'assigned_time' => 'required|string',
        ]);

        return redirect()->route('schedules.index')
            ->with('success', "Jadwal {$id} berhasil ditetapkan ke {$request->officer} pada {$request->assigned_date} pukul {$request->assigned_time}.");
    }
}
