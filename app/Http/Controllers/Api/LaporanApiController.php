<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanWarga;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LaporanApiController extends Controller
{
    /**
     * POST /api/laporan
     * Endpoint Flutter: warga mengirimkan pengaduan masalah kualitas air.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nama_pelapor' => ['required', 'string', 'max:100'],
                'nik_pelapor' => ['required', 'digits:16'],
                'no_wa' => ['required', 'string', 'regex:/^(08|\+628)[0-9]{7,11}$/'],
                'id_desa' => ['required', 'exists:desa,id_desa'],
                'rt' => ['required', 'string', 'max:5'],
                'rw' => ['required', 'string', 'max:5'],
                'kategori_laporan' => ['required', 'in:Air Bersih,Sanitasi,Sampah,Jentik Nyamuk,Limbah,PHBS,Lainnya'],
                'deskripsi' => ['required', 'string', 'min:20', 'max:1000'],
                'foto_bukti' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid. Periksa kembali isian Anda.',
                'errors' => $e->errors(),
            ], 422);
        }

        $pathFoto = null;
        if ($request->hasFile('foto_bukti')) {
            $pathFoto = $request->file('foto_bukti')
                ->store('bukti_laporan', 'public');
        }

        // Format: AIR-202609-A3F9 (unik per bulan)
        $kodeTiket = 'AIR-'.now()->format('Ym').'-'.strtoupper(Str::random(4));

        $laporan = LaporanWarga::create([
            'kode_tiket' => $kodeTiket,
            'nama_pelapor' => $validated['nama_pelapor'],
            'nik_pelapor' => $validated['nik_pelapor'],
            'no_wa' => $validated['no_wa'],
            'id_desa' => $validated['id_desa'],
            'rt' => $validated['rt'],
            'rw' => $validated['rw'],
            'kategori_laporan' => $validated['kategori_laporan'],
            'deskripsi' => $validated['deskripsi'],
            'foto_bukti' => $pathFoto,
            'status_laporan' => 'menunggu',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil dikirim. Simpan kode tiket Anda untuk memantau status.',
            'kode_tiket' => $laporan->kode_tiket,
            'id_laporan' => $laporan->id_laporan,
        ], 201);
    }

    /**
     * GET /api/laporan/{kode_tiket}/status
     * Flutter: cek status laporan berdasarkan kode tiket.
     */
    public function cekStatus(string $kodeTiket): JsonResponse
    {
        $laporan = LaporanWarga::with(['desa', 'jadwal.petugas'])
            ->where('kode_tiket', $kodeTiket)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'kode_tiket' => $laporan->kode_tiket,
            'status_laporan' => $laporan->status_laporan,
            'nama_pelapor' => $laporan->nama_pelapor,
            'jadwal' => $laporan->jadwal ? [
                'tanggal_kunjungan' => $laporan->jadwal->tanggal_kunjungan,
                'petugas' => $laporan->jadwal->petugas?->nama_petugas,
                'status_kunjungan' => $laporan->jadwal->status_kunjungan,
            ] : null,
        ]);
    }
}
