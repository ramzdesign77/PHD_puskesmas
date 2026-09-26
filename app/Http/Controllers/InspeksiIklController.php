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

        return view('inspeksi.form', compact('jadwal'));
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

        $validated = $request->validate([
            'jenis_sarana_air' => 'required|in:sumur_bor,sumur_terlindung,pdam,mata_air,lainnya',
            'jarak_sumber_pencemar' => 'required|integer|min:0|max:99999',
            'p1_dinding_sumur_retak' => 'nullable|boolean',
            'p2_penutup_tidak_rapat' => 'nullable|boolean',
            'p3_lantai_becek_retak' => 'nullable|boolean',
            'p4_spal_tersumbat' => 'nullable|boolean',
            'p5_air_keruh_berbau' => 'nullable|boolean',
            'rekomendasi_sanitarian' => 'required|string|min:5|max:2000',
            'tanggal_inspeksi' => 'required|date|before_or_equal:today',
        ]);

        // Hitung skor: jumlah kondisi berbahaya yang dicentang (true)
        $checklist = [
            'p1_dinding_sumur_retak',
            'p2_penutup_tidak_rapat',
            'p3_lantai_becek_retak',
            'p4_spal_tersumbat',
            'p5_air_keruh_berbau',
        ];

        $totalSkor = collect($checklist)
            ->filter(fn (string $key) => (bool) ($validated[$key] ?? false))
            ->count();

        $kategoriRisiko = InspeksiIkl::hitungKategoriRisiko($totalSkor);

        // ── Database transaction: insert inspeksi + update dua status ──────
        DB::transaction(function () use ($jadwal, $validated, $totalSkor, $kategoriRisiko): void {
            // 1. Simpan data inspeksi IKL
            InspeksiIkl::create([
                'id_jadwal' => $jadwal->id_jadwal,
                'jenis_sarana_air' => $validated['jenis_sarana_air'],
                'jarak_sumber_pencemar' => $validated['jarak_sumber_pencemar'],
                'p1_dinding_sumur_retak' => (bool) ($validated['p1_dinding_sumur_retak'] ?? false),
                'p2_penutup_tidak_rapat' => (bool) ($validated['p2_penutup_tidak_rapat'] ?? false),
                'p3_lantai_becek_retak' => (bool) ($validated['p3_lantai_becek_retak'] ?? false),
                'p4_spal_tersumbat' => (bool) ($validated['p4_spal_tersumbat'] ?? false),
                'p5_air_keruh_berbau' => (bool) ($validated['p5_air_keruh_berbau'] ?? false),
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
