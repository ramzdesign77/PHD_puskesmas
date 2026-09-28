<?php

namespace App\Http\Controllers;

use App\Models\LaporanWarga;
use Illuminate\Http\Request;

/**
 * Controller untuk warga web (bukan API Flutter).
 * Digunakan pada role 'citizen' / 'warga' di web app.
 */
class LaporanWargaController extends Controller
{
    /**
     * READ: Menampilkan & menyaring daftar laporan aduan.
     * Digunakan sebagai endpoint JSON internal jika diperlukan (bukan API publik).
     */
    public function index(Request $request)
    {
        $query = LaporanWarga::with('desa')->orderBy('id_laporan', 'desc');

        if ($request->filled('status_laporan')) {
            $query->where('status_laporan', $request->status_laporan);
        }

        if ($request->filled('kategori_laporan')) {
            $query->where('kategori_laporan', $request->kategori_laporan);
        }

        if ($request->filled('id_desa')) {
            $query->where('id_desa', $request->id_desa);
        }

        $laporan = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar laporan aduan berhasil dimuat',
            'data' => $laporan,
        ]);
    }

    /**
     * UPDATE: Memperbarui status laporan atau menolak laporan.
     */
    public function updateStatus(Request $request, int $idLaporan)
    {
        $laporan = LaporanWarga::find($idLaporan);

        if (! $laporan) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan tidak ditemukan',
            ], 404);
        }

        $validated = $request->validate([
            'status_laporan' => 'required|in:menunggu,dijadwalkan,selesai,ditolak',
            'alasan_penolakan' => 'nullable|string',
        ]);

        if ($validated['status_laporan'] === 'ditolak' && empty($validated['alasan_penolakan'])) {
            return response()->json([
                'success' => false,
                'message' => 'Alasan penolakan wajib diisi jika laporan ditolak!',
            ], 422);
        }

        $laporan->status_laporan = $validated['status_laporan'];
        $laporan->alasan_penolakan = ($validated['status_laporan'] === 'ditolak')
            ? $validated['alasan_penolakan']
            : null;

        $laporan->save();

        return response()->json([
            'success' => true,
            'message' => 'Status laporan berhasil diperbarui!',
            'data' => $laporan,
        ]);
    }
}
