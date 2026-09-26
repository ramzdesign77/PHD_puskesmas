<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\LaporanWarga;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Controller untuk endpoint API Flutter/Mobile.
 * Semua method mengembalikan JSON.
 */
class LaporanWargaApiController extends Controller
{
    /**
     * POST /api/laporan
     *
     * Endpoint untuk warga mengirim laporan masalah air via Flutter app.
     * Tidak memerlukan autentikasi (laporan anonim diperbolehkan).
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nama_pelapor' => 'required|string|max:150',
                'nik_pelapor' => 'nullable|digits:16',
                'no_wa' => 'nullable|string|max:20',
                'id_desa' => 'required|integer|exists:desa,id_desa',
                'rt' => 'required|string|max:5',
                'rw' => 'required|string|max:5',
                'kategori_laporan' => 'required|in:air_masalah,sanitasi_lingkungan',
                'deskripsi' => 'required|string|min:10|max:2000',
                'foto_bukti' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120',
            ]);

            $fotoBuktiPath = null;
            if ($request->hasFile('foto_bukti')) {
                $fotoBuktiPath = $request->file('foto_bukti')
                    ->store('laporan/foto_bukti', 'public');
            }

            $laporan = LaporanWarga::create([
                'kode_tiket' => LaporanWarga::generateKodeTiket(),
                'nama_pelapor' => $validated['nama_pelapor'],
                'nik_pelapor' => $validated['nik_pelapor'] ?? null,
                'no_wa' => $validated['no_wa'] ?? null,
                'id_desa' => $validated['id_desa'],
                'rt' => $validated['rt'],
                'rw' => $validated['rw'],
                'kategori_laporan' => $validated['kategori_laporan'],
                'deskripsi' => $validated['deskripsi'],
                'foto_bukti' => $fotoBuktiPath,
                'status_laporan' => 'menunggu',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Laporan berhasil dikirim. Tim Kesling akan segera menindaklanjuti.',
                'kode_tiket' => $laporan->kode_tiket,
                'id_laporan' => $laporan->id_laporan,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('LaporanWargaApi@store error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server. Coba lagi nanti.',
            ], 500);
        }
    }

    /**
     * GET /api/laporan/{kode_tiket}
     *
     * Warga dapat mengecek status laporan berdasarkan kode tiket.
     */
    public function statusByTiket(string $kodeTiket): JsonResponse
    {
        $laporan = LaporanWarga::with(['desa', 'jadwal.operator:id_user,nama_lengkap'])
            ->where('kode_tiket', $kodeTiket)
            ->first();

        if (! $laporan) {
            return response()->json([
                'success' => false,
                'message' => 'Kode tiket tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'kode_tiket' => $laporan->kode_tiket,
                'status_laporan' => $laporan->status_laporan,
                'kategori_laporan' => $laporan->kategori_laporan,
                'deskripsi' => $laporan->deskripsi,
                'desa' => $laporan->desa?->nama_desa,
                'rt' => $laporan->rt,
                'rw' => $laporan->rw,
                'created_at' => $laporan->created_at?->format('d M Y H:i'),
                'jadwal' => $laporan->jadwal ? [
                    'tanggal_kunjungan' => $laporan->jadwal->tanggal_kunjungan?->format('d M Y'),
                    'petugas' => $laporan->jadwal->operator?->nama_lengkap,
                    'status_kunjungan' => $laporan->jadwal->status_kunjungan,
                ] : null,
            ],
        ]);
    }

    /**
     * GET /api/desa
     *
     * Daftar desa untuk dropdown di Flutter app.
     */
    public function listDesa(): JsonResponse
    {
        $desa = Desa::orderBy('nama_desa')->get(['id_desa', 'nama_desa']);

        return response()->json([
            'success' => true,
            'data' => $desa,
        ]);
    }
}
