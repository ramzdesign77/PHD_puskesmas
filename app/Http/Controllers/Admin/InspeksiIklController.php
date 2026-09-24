<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInspeksiIklRequest;
use App\Models\InspeksiIkl;
use App\Models\JadwalInspeksi;
use App\Models\LaporanWarga;
use App\Models\Petugas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InspeksiIklController extends Controller
{
    /**
     * Halaman rekap semua hasil IKL Air yang sudah selesai diinspeksi.
     * Menggabungkan: laporan_warga + jadwal_inspeksi + petugas + inspeksi_ikl
     */
    public function index(Request $request): View
    {
        $query = JadwalInspeksi::query()
            ->with([
                'laporan.desa',
                'petugas',
                'inspeksiIkl',
            ])
            ->whereHas('inspeksiIkl');

        if ($request->filled('kategori_risiko')) {
            $query->whereHas('inspeksiIkl', fn ($q) => $q->where('kategori_risiko', $request->kategori_risiko));
        }

        if ($request->filled('id_operator')) {
            $query->where('id_operator', $request->id_operator);
        }

        $jadwalList = $query->orderBy('tanggal_kunjungan', 'desc')->paginate(15)->withQueryString();

        $rekapStatistik = InspeksiIkl::selectRaw("
            COUNT(*) as total_inspeksi,
            SUM(CASE WHEN kategori_risiko = 'aman'          THEN 1 ELSE 0 END) as jumlah_aman,
            SUM(CASE WHEN kategori_risiko = 'risiko_sedang' THEN 1 ELSE 0 END) as jumlah_sedang,
            SUM(CASE WHEN kategori_risiko = 'risiko_tinggi' THEN 1 ELSE 0 END) as jumlah_tinggi,
            ROUND(AVG(total_skor_ya), 1) as rata_rata_skor
        ")->first();

        $semuaPetugas = Petugas::where('is_active', 1)->orderBy('nama_petugas')->get();

        return view('admin.inspeksi-ikl.index', compact('jadwalList', 'rekapStatistik', 'semuaPetugas'));
    }

    /**
     * Halaman detail hasil inspeksi satu jadwal.
     */
    public function show(int $id_jadwal): View
    {
        $jadwal = JadwalInspeksi::with([
            'laporan.desa',
            'petugas',
            'inspeksiIkl',
            'paketAlat',
        ])->findOrFail($id_jadwal);

        abort_if(is_null($jadwal->inspeksiIkl), 404, 'Hasil inspeksi belum tersedia.');

        $labelParameter = InspeksiIkl::labelParameter();

        return view('admin.inspeksi-ikl.show', compact('jadwal', 'labelParameter'));
    }

    /**
     * Form input hasil inspeksi IKL oleh petugas lapangan.
     */
    public function create(int $id_jadwal): View
    {
        $jadwal = JadwalInspeksi::with(['laporan.desa', 'petugas'])
            ->findOrFail($id_jadwal);

        abort_if(
            ! is_null($jadwal->inspeksiIkl),
            422,
            'Hasil IKL untuk jadwal ini sudah pernah diinput.'
        );

        abort_unless(
            $jadwal->status_kunjungan === 'terjadwal',
            422,
            'Jadwal ini tidak dalam status aktif.'
        );

        $labelParameter = InspeksiIkl::labelParameter();

        return view('admin.inspeksi-ikl.create', compact('jadwal', 'labelParameter'));
    }

    /**
     * Simpan hasil inspeksi IKL + update status jadwal & laporan → selesai.
     */
    public function store(StoreInspeksiIklRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Hitung total skor dari jawaban "Ya" (true)
        $totalSkorYa = collect([
            $validated['p1_dinding_sumur_retak'],
            $validated['p2_penutup_tidak_rapat'],
            $validated['p3_lantai_becek_retak'],
            $validated['p4_spal_tersumbat'],
            $validated['p5_air_keruh_berbau'],
        ])->filter()->count();

        $kategoriRisiko = InspeksiIkl::hitungKategoriRisiko($totalSkorYa);

        $inspeksi = InspeksiIkl::create([
            'id_jadwal' => $validated['id_jadwal'],
            'jenis_sarana_air' => $validated['jenis_sarana_air'],
            'jarak_sumber_pencemar' => $validated['jarak_sumber_pencemar'],
            'p1_dinding_sumur_retak' => $validated['p1_dinding_sumur_retak'],
            'p2_penutup_tidak_rapat' => $validated['p2_penutup_tidak_rapat'],
            'p3_lantai_becek_retak' => $validated['p3_lantai_becek_retak'],
            'p4_spal_tersumbat' => $validated['p4_spal_tersumbat'],
            'p5_air_keruh_berbau' => $validated['p5_air_keruh_berbau'],
            'total_skor_ya' => $totalSkorYa,
            'kategori_risiko' => $kategoriRisiko,
            'rekomendasi_sanitarian' => $validated['rekomendasi_sanitarian'],
            'tanggal_inspeksi' => $validated['tanggal_inspeksi'],
        ]);

        // Update status jadwal → selesai
        $jadwal = JadwalInspeksi::find($validated['id_jadwal']);
        $jadwal->update(['status_kunjungan' => 'selesai']);

        // Update status laporan induk → selesai
        LaporanWarga::where('id_laporan', $jadwal->id_laporan)
            ->update(['status_laporan' => 'selesai']);

        $labelKategori = match ($inspeksi->kategori_risiko) {
            'aman' => 'Aman',
            'risiko_sedang' => 'Risiko Sedang',
            'risiko_tinggi' => 'Risiko Tinggi',
            default => $inspeksi->kategori_risiko,
        };

        return redirect()->route('admin.inspeksi-ikl.index')
            ->with('success', "Hasil IKL berhasil disimpan. Kategori Risiko: {$labelKategori} (Skor: {$totalSkorYa}/5).");
    }
}
