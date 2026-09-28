<?php

namespace App\Http\Controllers;

use App\Models\LaporanWarga;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private function getMockReports(): array
    {
        return [
            ['id' => 'REP-001', 'citizen' => 'Ahmad Fauzi',   'category' => 'Air Bersih',      'description' => 'Sumur warga tercemar limbah industri sekitar',              'location' => 'Jl. Mawar No.5, RT 03/02',      'status' => 'pending',     'date' => '2026-09-10', 'image' => true],
            ['id' => 'REP-002', 'citizen' => 'Siti Rahayu',   'category' => 'Sanitasi',         'description' => 'Saluran pembuangan tersumbat sudah 2 minggu',               'location' => 'Jl. Melati No.12, RT 01/05',    'status' => 'in_progress', 'date' => '2026-09-11', 'image' => false],
            ['id' => 'REP-003', 'citizen' => 'Budi Prakoso',  'category' => 'Sampah',           'description' => 'Tumpukan sampah tidak terangkut lebih dari 5 hari',         'location' => 'Jl. Dahlia No.8, RT 02/03',     'status' => 'resolved',    'date' => '2026-09-08', 'image' => true],
            ['id' => 'REP-004', 'citizen' => 'Dewi Lestari',  'category' => 'Jentik Nyamuk',    'description' => 'Banyak genangan air di sekitar rumah warga berpotensi DBD',  'location' => 'Gang Cempaka No.3, RT 04/01',   'status' => 'pending',     'date' => '2026-09-13', 'image' => true],
            ['id' => 'REP-005', 'citizen' => 'Rizki Hidayat', 'category' => 'Air Bersih',       'description' => 'Air PDAM berwarna kekuningan dan berbau',                   'location' => 'Jl. Kenanga No.20, RT 05/04',   'status' => 'in_progress', 'date' => '2026-09-14', 'image' => false],
            ['id' => 'REP-006', 'citizen' => 'Rina Susanti',  'category' => 'Sanitasi',         'description' => 'Toilet umum rusak dan tidak layak pakai',                   'location' => 'Pasar Sumbersari Kios No.15',   'status' => 'resolved',    'date' => '2026-09-07', 'image' => true],
            ['id' => 'REP-007', 'citizen' => 'Hendra Wijaya', 'category' => 'Limbah',           'description' => 'Pembuangan limbah pabrik langsung ke sungai',               'location' => 'Jl. Industri No.45, RT 02/06', 'status' => 'pending',     'date' => '2026-09-15', 'image' => true],
            ['id' => 'REP-008', 'citizen' => 'Yuni Kartika',  'category' => 'Jentik Nyamuk',   'description' => 'Kolam ikan warga tidak terawat menjadi sarang nyamuk',      'location' => 'Jl. Teratai No.7, RT 01/02',   'status' => 'in_progress', 'date' => '2026-09-16', 'image' => false],
        ];
    }

    public function index()
    {
        $reports = $this->getMockReports();
        $role    = session('role');

        $summary = [
            'total'       => count($reports),
            'pending'     => count(array_filter($reports, fn($r) => $r['status'] === 'pending')),
            'in_progress' => count(array_filter($reports, fn($r) => $r['status'] === 'in_progress')),
            'resolved'    => count(array_filter($reports, fn($r) => $r['status'] === 'resolved')),
        ];

        return view('reports.index', compact('reports', 'role', 'summary'));
    }

    public function analytics()
    {
        abort_unless(session('role') === 'admin', 403);

        $reports = $this->getMockReports();
        $summary = [
            'total' => count($reports),
            'pending' => count(array_filter($reports, fn ($report) => $report['status'] === 'pending')),
            'in_progress' => count(array_filter($reports, fn ($report) => $report['status'] === 'in_progress')),
            'resolved' => count(array_filter($reports, fn ($report) => $report['status'] === 'resolved')),
        ];

        $categories = collect($reports)
            ->groupBy('category')
            ->map(fn ($categoryReports) => count($categoryReports))
            ->sortDesc();

        return view('analytics.index', compact('summary', 'categories', 'reports'));
    }


    public function store(Request $request){

        $validated = $request->validate([
            'category'    => 'required|string',
            'description' => 'required|string|min:10',
            'location'    => 'required|string',
            'id_desa'     => 'required|exists:desa,id_desa',
        ]);

        LaporanWarga::create([
            'kode_tiket'       => 'TCK-' . strtoupper(uniqid()),
            'nama_pelapor'     => session('user_name', 'Anonim'),
            'nik_pelapor'      => null,
            'no_wa'            => null,
            'id_desa'          => $validated['id_desa'],
            'kategori_laporan' => $validated['category'],
            'deskripsi'        => $validated['description'] . ' | Lokasi: ' . $validated['location'],
            'foto_bukti'       => $request->hasFile('photo') ? $request->file('photo')->store('bukti_laporan', 'public') : null,
            'status_laporan'   => 'menunggu',
        ]);

        return redirect()->route('reports.index')
            ->with('success', 'Laporan berhasil dikirim! Tim Kesling akan segera menindaklanjuti laporan Anda.');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:pending,in_progress,resolved']);
        return redirect()->route('reports.index')
            ->with('success', "Status laporan #{$id} berhasil diperbarui menjadi: {$request->status}");
    }
}
