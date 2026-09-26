<?php

namespace App\Http\Controllers;

use App\Models\JadwalInspeksi;
use App\Models\LaporanWarga;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Controller untuk halaman Admin – Tab Laporan Masuk & Penjadwalan (Tab 1)
 * serta Rekap Hasil Inspeksi IKL Air (Tab 2).
 *
 * Route yang dilayani:
 *  GET  /reports          → index()
 *  POST /reports/jadwalkan/{id_laporan} → jadwalkan()
 *  POST /reports/tolak/{id_laporan}     → tolak()
 *  POST /reports/reschedule/{id_jadwal} → reschedule()
 */
class ReportController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // READ: Halaman utama (Tab 1 + Tab 2)
    // ─────────────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $role = session('role');

        // ── Tab 1: Laporan aktif (menunggu & dijadwalkan) ──────────────────
        $queryAktif = LaporanWarga::with(['desa', 'jadwal.operator'])
            ->whereIn('status_laporan', ['menunggu', 'dibaca', 'diterima', 'dijadwalkan'])
            ->orderBy('id_laporan', 'desc');

        if ($request->filled('id_desa')) {
            $queryAktif->where('id_desa', $request->id_desa);
        }
        if ($request->filled('status_laporan')) {
            $queryAktif->where('status_laporan', $request->status_laporan);
        }

        $laporanAktif = $queryAktif->paginate(15, ['*'], 'page_aktif')
            ->withQueryString();

        // ── Tab 2: Rekap hasil inspeksi IKL (selesai) – JOIN 4 tabel ──────
        $rekapIkl = $this->queryRekapIkl($request)->paginate(15, ['*'], 'page_rekap')
            ->withQueryString();

        // ── Summary cards ─────────────────────────────────────────────────
        $summary = $this->getSummary();

        // ── Daftar sanitarian aktif untuk dropdown modal jadwal ───────────
        $sanitarians = User::where('is_active', true)
            ->whereIn('role', ['sanitarian', 'staf_backup_kluster4'])
            ->orderBy('nama_lengkap')
            ->get(['id_user', 'nama_lengkap', 'wilayah_kerja']);

        return view('reports.index', compact(
            'laporanAktif',
            'rekapIkl',
            'summary',
            'sanitarians',
            'role'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ACTION: Jadwalkan laporan (insert jadwal + ubah status)
    // ─────────────────────────────────────────────────────────────────────────

    public function jadwalkan(Request $request, int $idLaporan)
    {
        $validated = $request->validate([
            'id_operator' => 'required|exists:users,id_user',
            'tanggal_kunjungan' => 'required|date|after_or_equal:today',
            'jenis_kunjungan' => 'required|in:ikl_laporan_warga,ikl_rutin_rt',
        ]);

        $laporan = LaporanWarga::findOrFail($idLaporan);

        // Pastikan laporan belum dijadwalkan sebelumnya
        if (! in_array($laporan->status_laporan, ['menunggu', 'dibaca', 'diterima'])) {
            return back()->with('error', 'Laporan ini sudah dijadwalkan atau tidak bisa dijadwalkan.');
        }

        // ── Database transaction: ubah status + insert jadwal atomically ──
        DB::transaction(function () use ($laporan, $validated): void {
            $laporan->update(['status_laporan' => 'dijadwalkan']);

            JadwalInspeksi::create([
                'id_laporan' => $laporan->id_laporan,
                'id_operator' => $validated['id_operator'],
                'tanggal_kunjungan' => $validated['tanggal_kunjungan'],
                'jenis_kunjungan' => $validated['jenis_kunjungan'],
                'status_kunjungan' => 'terjadwal',
            ]);
        });

        return back()->with('success', "Laporan #{$laporan->kode_tiket} berhasil dijadwalkan.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ACTION: Tolak laporan
    // ─────────────────────────────────────────────────────────────────────────

    public function tolak(Request $request, int $idLaporan)
    {
        $validated = $request->validate([
            'alasan_penolakan' => 'required|string|min:5|max:500',
        ]);

        $laporan = LaporanWarga::findOrFail($idLaporan);

        $laporan->update([
            'status_laporan' => 'ditolak',
            'alasan_penolakan' => $validated['alasan_penolakan'],
        ]);

        return back()->with('success', "Laporan #{$laporan->kode_tiket} telah ditolak.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ACTION: Reschedule / re-assign jadwal
    // ─────────────────────────────────────────────────────────────────────────

    public function reschedule(Request $request, int $idJadwal)
    {
        $validated = $request->validate([
            'id_operator' => 'required|exists:users,id_user',
            'tanggal_kunjungan' => 'required|date|after_or_equal:today',
        ]);

        $jadwal = JadwalInspeksi::where('status_kunjungan', 'terjadwal')
            ->findOrFail($idJadwal);

        $jadwal->update([
            'id_operator' => $validated['id_operator'],
            'tanggal_kunjungan' => $validated['tanggal_kunjungan'],
        ]);

        return back()->with('success', 'Jadwal berhasil diperbarui.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EXPORT: Ekspor rekap IKL ke CSV
    // ─────────────────────────────────────────────────────────────────────────

    public function export(Request $request)
    {
        $data = $this->queryRekapIkl($request)->get();

        $filename = 'rekap_ikl_air_'.now()->format('Ymd_His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data): void {
            $handle = fopen('php://output', 'w');

            // BOM untuk Excel agar terbaca UTF-8 dengan benar
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header baris
            fputcsv($handle, [
                'Kode Tiket',
                'Nama Pelapor',
                'Desa',
                'RT/RW',
                'Kategori Laporan',
                'Tanggal Laporan',
                'Petugas Sanitarian',
                'Tanggal Kunjungan',
                'Jenis Sarana Air',
                'Jarak Sumber Pencemar (m)',
                'Dinding Retak',
                'Penutup Tidak Rapat',
                'Lantai Becek/Retak',
                'SPAL Tersumbat',
                'Air Keruh/Berbau',
                'Total Skor',
                'Kategori Risiko',
                'Rekomendasi Sanitarian',
            ]);

            foreach ($data as $row) {
                fputcsv($handle, [
                    $row->kode_tiket,
                    $row->nama_pelapor,
                    $row->nama_desa,
                    "RT {$row->rt}/RW {$row->rw}",
                    $row->kategori_laporan,
                    $row->created_at,
                    $row->nama_petugas,
                    $row->tanggal_kunjungan,
                    $row->jenis_sarana_air,
                    $row->jarak_sumber_pencemar,
                    $row->p1_dinding_sumur_retak ? 'Ya' : 'Tidak',
                    $row->p2_penutup_tidak_rapat ? 'Ya' : 'Tidak',
                    $row->p3_lantai_becek_retak ? 'Ya' : 'Tidak',
                    $row->p4_spal_tersumbat ? 'Ya' : 'Tidak',
                    $row->p5_air_keruh_berbau ? 'Ya' : 'Tidak',
                    $row->total_skor_ya,
                    $row->kategori_risiko,
                    $row->rekomendasi_sanitarian,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE: Query Rekap IKL (Tab 2) – JOIN 4 tabel
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Query Eloquent JOIN lengkap untuk Tab 2 (Rekap Hasil Inspeksi IKL Air).
     *
     * Mengambil data dari: laporan_warga → jadwal_inspeksi → users → inspeksi_ikl
     * Hanya laporan berstatus 'selesai' dengan data inspeksi yang lengkap.
     */
    private function queryRekapIkl(Request $request)
    {
        $query = DB::table('laporan_warga as lw')
            ->join('jadwal_inspeksi as ji', 'lw.id_laporan', '=', 'ji.id_laporan')
            ->join('users as u', 'ji.id_operator', '=', 'u.id_user')
            ->join('inspeksi_ikl as ikl', 'ji.id_jadwal', '=', 'ikl.id_jadwal')
            ->leftJoin('desa as d', 'lw.id_desa', '=', 'd.id_desa')
            ->where('lw.status_laporan', 'selesai')
            ->where('ji.status_kunjungan', 'selesai')
            ->select([
                'lw.id_laporan',
                'lw.kode_tiket',
                'lw.nama_pelapor',
                'lw.kategori_laporan',
                'lw.rt',
                'lw.rw',
                'lw.created_at',
                'd.nama_desa',
                'u.nama_lengkap as nama_petugas',
                'u.wilayah_kerja',
                'ji.tanggal_kunjungan',
                'ji.id_jadwal',
                'ikl.id_inspeksi',
                'ikl.jenis_sarana_air',
                'ikl.jarak_sumber_pencemar',
                'ikl.p1_dinding_sumur_retak',
                'ikl.p2_penutup_tidak_rapat',
                'ikl.p3_lantai_becek_retak',
                'ikl.p4_spal_tersumbat',
                'ikl.p5_air_keruh_berbau',
                'ikl.total_skor_ya',
                'ikl.kategori_risiko',
                'ikl.rekomendasi_sanitarian',
                'ikl.tanggal_inspeksi',
            ])
            ->orderBy('ji.tanggal_kunjungan', 'desc');

        // Filter opsional dari request
        if ($request->filled('kategori_risiko')) {
            $query->where('ikl.kategori_risiko', $request->kategori_risiko);
        }
        if ($request->filled('id_petugas')) {
            $query->where('u.id_user', $request->id_petugas);
        }
        if ($request->filled('dari_tanggal')) {
            $query->whereDate('ji.tanggal_kunjungan', '>=', $request->dari_tanggal);
        }
        if ($request->filled('sampai_tanggal')) {
            $query->whereDate('ji.tanggal_kunjungan', '<=', $request->sampai_tanggal);
        }

        return $query;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE: Summary counts
    // ─────────────────────────────────────────────────────────────────────────

    private function getSummary(): array
    {
        return [
            'menunggu' => LaporanWarga::whereIn('status_laporan', ['menunggu', 'dibaca'])->count(),
            'dijadwalkan' => LaporanWarga::where('status_laporan', 'dijadwalkan')->count(),
            'selesai' => LaporanWarga::where('status_laporan', 'selesai')->count(),
            'ditolak' => LaporanWarga::where('status_laporan', 'ditolak')->count(),
            'total' => LaporanWarga::count(),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // LEGACY: Compat method untuk route lama reports.store & reports.status
    // ─────────────────────────────────────────────────────────────────────────

    /** @deprecated Gunakan jadwalkan() dan tolak(). */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kategori_laporan' => 'required|string',
            'deskripsi' => 'required|string|min:10',
            'id_desa' => 'required|exists:desa,id_desa',
        ]);

        LaporanWarga::create([
            'kode_tiket' => LaporanWarga::generateKodeTiket(),
            'nama_pelapor' => session('user_name', 'Anonim'),
            'nik_pelapor' => null,
            'no_wa' => null,
            'id_desa' => $validated['id_desa'],
            'rt' => $request->rt ?? '00',
            'rw' => $request->rw ?? '00',
            'kategori_laporan' => $validated['kategori_laporan'],
            'deskripsi' => $validated['deskripsi'],
            'foto_bukti' => $request->hasFile('photo')
                ? $request->file('photo')->store('bukti_laporan', 'public')
                : null,
            'status_laporan' => 'menunggu',
        ]);

        return redirect()->route('reports.index')
            ->with('success', 'Laporan berhasil dikirim! Tim Kesling akan segera menindaklanjuti.');
    }

    /** @deprecated */
    public function updateStatus(Request $request, $id)
    {
        return redirect()->route('reports.index')
            ->with('info', 'Gunakan endpoint jadwalkan atau tolak untuk mengubah status laporan.');
    }

    public function analytics()
    {
        abort_unless(in_array(session('role'), ['admin', 'kepala_puskesmas']), 403);

        $summary = $this->getSummary();
        $rekapIkl = $this->queryRekapIkl(request())->get();

        // Distribusi kategori risiko untuk chart
        $distribusiRisiko = $rekapIkl->groupBy('kategori_risiko')
            ->map->count();

        return view('analytics.index', compact('summary', 'distribusiRisiko'));
    }
}
