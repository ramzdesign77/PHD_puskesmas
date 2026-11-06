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
     * Menggabungkan: trx_laporan_warga + trx_jadwal_inspeksi + app_users + trx_inspeksi_ikl
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
        $indikatorList = $jadwal->inspeksiIkl->detail_indikator
            ?? InspeksiIkl::buildIndicatorDetails(
                $jadwal->inspeksiIkl->kategori_ikl ?? 'sumur_gali',
                collect(range(1, 7))->mapWithKeys(fn (int $number): array => ['p'.$number => (bool) ($jadwal->inspeksiIkl->{'p'.$number} ?? false)])->all(),
            );

        return view('admin.inspeksi-ikl.show', compact('jadwal', 'labelParameter', 'indikatorList'));
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

        $kategoriIkl = $jadwal->laporan?->kategori_laporan === 'sarang_nyamuk_dbd'
            ? 'sarang_nyamuk_dbd'
            : ($jadwal->laporan?->kategori_laporan === 'jamban_limbah_stbm'
                ? 'jamban_limbah_stbm'
                : ($jadwal->laporan?->kategori_laporan === 'tumpukan_sampah' ? 'tumpukan_sampah' : 'sumur_gali'));
        $pertanyaan = InspeksiIkl::getQuestionsByCategory($kategoriIkl);
        $bankPertanyaan = collect(['sumur_gali', 'sumur_bor', 'sarang_nyamuk_dbd', 'jamban_limbah_stbm', 'tumpukan_sampah'])
            ->mapWithKeys(fn (string $category): array => [$category => InspeksiIkl::getQuestionsByCategory($category)])
            ->all();

        return view('admin.inspeksi-ikl.create', compact('jadwal', 'kategoriIkl', 'pertanyaan', 'bankPertanyaan'));
    }

    /**
     * Simpan hasil inspeksi IKL + update status jadwal & laporan → selesai.
     */
    public function store(StoreInspeksiIklRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Hitung total skor dari jawaban "Ya" (true)
        $answers = collect(range(1, 7))->mapWithKeys(fn (int $number): array => [
            'p'.$number => $number < 6 ? (bool) $validated['p'.$number] : (bool) $validated['p'.$number.'_indikator_'.$number],
        ])->all();
        $totalSkorYa = count(array_filter($answers));
        $risk = InspeksiIkl::calculateRiskCategory($totalSkorYa, $validated['kategori_ikl'], $answers);
        $kategoriRisiko = $risk['code'];

        $inspeksi = InspeksiIkl::create([
            'id_jadwal' => $validated['id_jadwal'],
            'jenis_sarana_air' => $validated['jenis_sarana_air'],
            'kategori_ikl' => $validated['kategori_ikl'],
            'jarak_sumber_pencemar' => $validated['jarak_sumber_pencemar'],
            'p1_dinding_sumur_retak' => $answers['p1'],
            'p2_penutup_tidak_rapat' => $answers['p2'],
            'p3_lantai_becek_retak' => $answers['p3'],
            'p4_spal_tersumbat' => $answers['p4'],
            'p5_air_keruh_berbau' => $answers['p5'],
            'p1' => $answers['p1'],
            'p2' => $answers['p2'],
            'p3' => $answers['p3'],
            'p4' => $answers['p4'],
            'p5' => $answers['p5'],
            'p6_indikator_6' => $answers['p6'],
            'p7_indikator_7' => $answers['p7'],
            'detail_indikator' => InspeksiIkl::buildIndicatorDetails($validated['kategori_ikl'], $answers),
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
            'risiko_amat_tinggi' => 'Risiko Amat Tinggi',
            default => $inspeksi->kategori_risiko,
        };

        return redirect()->route('admin.inspeksi-ikl.index')
            ->with('success', "Hasil IKL berhasil disimpan. Kategori Risiko: {$labelKategori} (Skor: {$totalSkorYa}/7).");
    }
}
