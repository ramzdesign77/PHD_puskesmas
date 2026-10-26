<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReportStoreRequest;
use App\Models\Report;
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

        $report = Report::query()->create([
            'nama_lengkap' => $validated['nama_lengkap'],
            'nik' => $validated['nik'],
            'nomor_telepon' => $validated['nomor_telepon'],
            'village_id' => $validated['village_id'],
            'rt' => $validated['rt'],
            'rw' => $validated['rw'],
            'kategori_laporan' => $validated['kategori_laporan'],
            'deskripsi_masalah' => $validated['deskripsi_masalah'],
            'foto_bukti' => $fotoBuktiPath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil dikirim!',
            'data' => [
                'id' => $report->id,
                'nama_lengkap' => $report->nama_lengkap,
                'nik' => $report->nik,
                'nomor_telepon' => $report->nomor_telepon,
                'village_id' => $report->village_id,
                'rt' => $report->rt,
                'rw' => $report->rw,
                'kategori_laporan' => $report->kategori_laporan,
                'deskripsi_masalah' => $report->deskripsi_masalah,
                'foto_bukti' => $report->foto_bukti,
                'status' => $report->status,
                'created_at' => $report->created_at,
                'updated_at' => $report->updated_at,
            ],
        ], 201);
    }
}
