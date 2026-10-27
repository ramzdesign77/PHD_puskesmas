<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReportStoreRequest;
use App\Models\LaporanWarga;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class ReportController extends Controller
{
    public function store(ReportStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $fotoBuktiPath = $request->file('foto_bukti')?->store('reports', 'public');

        if ($fotoBuktiPath === false) {
            throw new RuntimeException('Gagal menyimpan foto bukti laporan.');
        }

        $laporan = LaporanWarga::query()->create([
            'kode_tiket' => LaporanWarga::generateKodeTiket(),
            'nama_pelapor' => $validated['nama_lengkap'],
            'nik_pelapor' => $validated['nik'],
            'no_wa' => $validated['nomor_telepon'],
            'id_desa' => $validated['village_id'],
            'rt' => $validated['rt'],
            'rw' => $validated['rw'],
            'kategori_laporan' => $validated['kategori_laporan'] === 'Masalah air'
                ? 'air_masalah'
                : 'sanitasi_lingkungan',
            'deskripsi' => $validated['deskripsi_masalah'],
            'foto_bukti' => $fotoBuktiPath,
            'status_laporan' => 'menunggu',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil dikirim!',
            'data' => [
                'id' => $laporan->id_laporan,
                'nama_lengkap' => $laporan->nama_pelapor,
                'nik' => $laporan->nik_pelapor,
                'nomor_telepon' => $laporan->no_wa,
                'village_id' => $laporan->id_desa,
                'rt' => $laporan->rt,
                'rw' => $laporan->rw,
                'kategori_laporan' => $validated['kategori_laporan'],
                'deskripsi_masalah' => $laporan->deskripsi,
                'foto_bukti' => $laporan->foto_bukti,
                'status' => 'Pendataan/Pending',
                'created_at' => $laporan->created_at,
                'updated_at' => null,
            ],
        ], 201);
    }
}
