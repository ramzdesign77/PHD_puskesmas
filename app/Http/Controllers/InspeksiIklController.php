<?php

namespace App\Http\Controllers;

use App\Models\InspeksiIkl;
use App\Models\JadwalInspeksi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Controller untuk Petugas Sanitarian mengisi Form IKL Air.
 *
 * Flow:
 *   1. Petugas membuka halaman form IKL dari jadwal mereka.
 *   2. Mengisi checklist kondisi fisik sarana air.
 *   3. Submit → inspeksi_ikl tersimpan, status_kunjungan & status_laporan → 'selesai'.
 */
class InspeksiIklController extends Controller
{
    /**
     * GET /inspeksi/{id_jadwal}/form
     *
     * Tampilkan form input hasil IKL untuk jadwal tertentu.
     * Hanya petugas yang ditugaskan yang bisa mengisi.
     */
    public function form(int $idJadwal)
    {
        $jadwal = JadwalInspeksi::with(['laporan.desa', 'operator'])
            ->where('status_kunjungan', 'terjadwal')
            ->findOrFail($idJadwal);

        // Pastikan hanya operator yang ditugaskan yang bisa akses
        $currentUserId = session('user_id');
        if ($jadwal->id_operator !== $currentUserId && session('role') !== 'admin') {
            abort(403, 'Anda tidak berwenang mengisi inspeksi ini.');
        }

        $kategoriIkl = $jadwal->laporan?->kategori_laporan === 'sarang_nyamuk_dbd'
            ? 'sarang_nyamuk_dbd'
            : ($jadwal->laporan?->kategori_laporan === 'jamban_limbah_stbm'
                ? 'jamban_limbah_stbm'
                : ($jadwal->laporan?->kategori_laporan === 'tumpukan_sampah'
                    ? 'tumpukan_sampah'
                    : ($jadwal->laporan?->kategori_laporan === 'air_sumur_keruh_berbau' ? 'sumur_bor' : 'sumur_gali')));
        $pertanyaan = InspeksiIkl::getQuestionsByCategory($kategoriIkl);
        $bankPertanyaan = collect(['sumur_gali', 'sumur_bor', 'sarang_nyamuk_dbd', 'jamban_limbah_stbm', 'tumpukan_sampah'])
            ->mapWithKeys(fn (string $category): array => [$category => InspeksiIkl::getQuestionsByCategory($category)])
            ->all();

        return view('inspeksi.form', compact('jadwal', 'kategoriIkl', 'pertanyaan', 'bankPertanyaan'));
    }

    /**
     * POST /inspeksi/{id_jadwal}/submit
     *
     * Simpan hasil IKL Air, lalu otomatis update status kunjungan & laporan ke 'selesai'.
     *
     * State machine yang dijalankan (dalam 1 transaction):
     *   inspeksi_ikl (INSERT)
     *   jadwal_inspeksi.status_kunjungan → 'selesai'
     *   laporan_warga.status_laporan     → 'selesai'  (jika berasal dari laporan warga)
     */
    public function submit(Request $request, int $idJadwal): RedirectResponse
    {
        $jadwal = JadwalInspeksi::with('laporan')
            ->where('status_kunjungan', 'terjadwal')
            ->findOrFail($idJadwal);

        // Pastikan operator yang benar atau admin
        $currentUserId = session('user_id');
        if ($jadwal->id_operator !== $currentUserId && session('role') !== 'admin') {
            abort(403, 'Anda tidak berwenang mengirim inspeksi ini.');
        }

        $request->merge([
            'kategori_ikl' => $request->input('kategori_ikl', $request->input('jenis_sarana_air') === 'sumur_bor' ? 'sumur_bor' : 'sumur_gali'),
            'p1' => $request->boolean('p1') || $request->boolean('p1_dinding_sumur_retak'),
            'p2' => $request->boolean('p2') || $request->boolean('p2_penutup_tidak_rapat'),
            'p3' => $request->boolean('p3') || $request->boolean('p3_lantai_becek_retak'),
            'p4' => $request->boolean('p4') || $request->boolean('p4_spal_tersumbat'),
            'p5' => $request->boolean('p5') || $request->boolean('p5_air_keruh_berbau'),
            'p6_indikator_6' => $request->boolean('p6_indikator_6') || $request->boolean('p6') || $request->boolean('indikator_6'),
            'p7_indikator_7' => $request->boolean('p7_indikator_7') || $request->boolean('p7') || $request->boolean('indikator_7'),
        ]);

        $validated = $request->validate([
            'jenis_sarana_air' => 'required|in:sumur_bor,sumur_terlindung,pdam,mata_air,lainnya',
            'kategori_ikl' => 'required|in:sumur_gali,sumur_bor,sarang_nyamuk_dbd,jamban_limbah_stbm,tumpukan_sampah',
            'jarak_sumber_pencemar' => 'required|integer|min:0|max:99999',
            'p1' => 'nullable|boolean',
            'p2' => 'nullable|boolean',
            'p3' => 'nullable|boolean',
            'p4' => 'nullable|boolean',
            'p5' => 'nullable|boolean',
            'p6_indikator_6' => 'nullable|boolean',
            'p7_indikator_7' => 'nullable|boolean',
            'rekomendasi_sanitarian' => 'required|string|min:5|max:2000',
            'tanggal_inspeksi' => 'required|date|before_or_equal:today',
        ]);

        // Hitung skor: jumlah kondisi berbahaya yang dicentang (true)
        $answers = collect(range(1, 7))->mapWithKeys(fn (int $number): array => [
            'p'.$number => (bool) ($validated[$number < 6 ? 'p'.$number : 'p'.$number.'_indikator_'.$number] ?? false),
        ])->all();
        $totalSkor = count(array_filter($answers));
        $kategoriRisiko = InspeksiIkl::calculateRiskCategory($totalSkor, $validated['kategori_ikl'], $answers)['code'];

        // ── Database transaction: insert inspeksi + update dua status ──────
        DB::transaction(function () use ($jadwal, $validated, $totalSkor, $kategoriRisiko, $answers): void {
            // 1. Simpan data inspeksi IKL
            InspeksiIkl::create([
                'id_jadwal' => $jadwal->id_jadwal,
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
                'total_skor_ya' => $totalSkor,
                'kategori_risiko' => $kategoriRisiko,
                'rekomendasi_sanitarian' => $validated['rekomendasi_sanitarian'],
                'tanggal_inspeksi' => $validated['tanggal_inspeksi'],
            ]);

            // 2. Update status jadwal → selesai
            $jadwal->update(['status_kunjungan' => 'selesai']);

            // 3. Update status laporan → selesai (jika bukan IKL rutin tanpa laporan)
            if ($jadwal->laporan !== null) {
                $jadwal->laporan->update(['status_laporan' => 'selesai']);
            }
        });

        return redirect()->route('schedules.index')
            ->with('success', 'Hasil inspeksi IKL berhasil disimpan. Status laporan telah diperbarui menjadi Selesai.');
    }

    /**
     * GET /inspeksi/{id_inspeksi}
     *
     * Detail hasil inspeksi (untuk admin / kepala puskesmas).
     */
    public function show(int $idInspeksi)
    {
        $inspeksi = InspeksiIkl::with([
            'jadwal.laporan.desa',
            'jadwal.operator',
        ])->findOrFail($idInspeksi);

        return view('inspeksi.show', compact('inspeksi'));
    }
}
