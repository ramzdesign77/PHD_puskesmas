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

        // $reports = $this->getMockReports();
        $reports = LaporanWarga::all();
        $role    = session('role');

        $summary = [
            'total'       => $reports->count(),
            'pending'     => $reports->where('status_laporan','menunggu')->count(),
            'in_progress' => $reports->where('status_laporan','in_progress')->count(),
            'resolved'    => $reports->where('status_laporan','ditolak')->count(),
        ];

        return view('reports.'.$role, compact('reports', 'role', 'summary'));
    }

    public function show($id){
        $report = LaporanWarga::where('id_laporan',$id)->firstOrFail();
        return view('reports.show', compact('report'));
    }

    public function analytics(){
        abort_unless(session('role') === 'admin', 403);

        $reports = $this->getMockReports();
        $summary = [
            'total' => count($reports),
            'pending' => count(array_filter($reports, fn ($report) => $report['status_laporan'] === 'pending')),
            'in_progress' => count(array_filter($reports, fn ($report) => $report['status_laporan'] === 'in_progress')),
            'resolved' => count(array_filter($reports, fn ($report) => $report['status_laporan'] === 'resolved')),
        ];

        $categories = collect($reports)
            ->groupBy('category')
            ->map(fn ($categoryReports) => count($categoryReports))
            ->sortDesc();

        return view('analytics.index', compact('summary', 'categories', 'reports'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category'    => 'required|string',
            'description' => 'required|string|min:10',
            'location'    => 'required|string',
        ]);

        return redirect()->route('reports.index')
            ->with('success', 'Laporan berhasil dikirim! Tim Kesling akan segera menindaklanjuti laporan Anda.');
    }

    public function updateStatus(Request $request, $id){
        $request->validate([
            'status' => 'required|in:menunggu,in_progress,selesai', // samakan dgn value di DB
            'alasan_penolakan' => 'required_if:status,ditolak|nullable|string|max:255',
        ]);

        $report = LaporanWarga::findOrFail($id);
        $report->status_laporan = $request->status;

        $report->alasan_penolakan = $request->status === 'ditolak'
        ? $request->alasan_penolakan
        : null;

        $report->save();

        return redirect()->route('reports.index')
            ->with('success', "Status laporan #{$id} berhasil diperbarui.");
    }
}
