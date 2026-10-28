<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InspeksiIkl;
use App\Models\JadwalInspeksi;
use App\Models\LaporanWarga;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InspectionController extends Controller
{
    private const CHECKLIST_FIELDS = [
        'p1_dinding_sumur_retak',
        'p2_penutup_tidak_rapat',
        'p3_lantai_becek_retak',
        'p4_spal_tersumbat',
        'p5_air_keruh_berbau',
    ];

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_jadwal' => ['required', 'integer', 'min:1'],
            'jenis_sarana_air' => ['required', 'in:sumur_bor,sumur_terlindung,pdam,mata_air,lainnya'],
            'jarak_sumber_pencemar' => ['required', 'integer', 'min:0', 'max:99999'],
            'p1_dinding_sumur_retak' => ['sometimes', 'boolean'],
            'p2_penutup_tidak_rapat' => ['sometimes', 'boolean'],
            'p3_lantai_becek_retak' => ['sometimes', 'boolean'],
            'p4_spal_tersumbat' => ['sometimes', 'boolean'],
            'p5_air_keruh_berbau' => ['sometimes', 'boolean'],
            'rekomendasi_sanitarian' => ['required', 'string', 'min:5', 'max:2000'],
            'tanggal_inspeksi' => ['required', 'date', 'before_or_equal:today'],
        ]);

        /** @var User $officer */
        $officer = $request->user();

        $result = DB::transaction(function () use ($validated, $officer): array {
            $jadwal = JadwalInspeksi::query()
                ->whereKey($validated['id_jadwal'])
                ->lockForUpdate()
                ->first();

            if ($jadwal === null) {
                abort(404, 'Jadwal inspeksi tidak ditemukan.');
            }

            if ((int) $jadwal->id_operator !== (int) $officer->id_user) {
                abort(403, 'Jadwal ini tidak ditugaskan kepada akun Anda.');
            }

            if ($jadwal->status_kunjungan !== 'terjadwal') {
                abort(409, 'Jadwal inspeksi ini sudah tidak aktif.');
            }

            if ($jadwal->inspeksiIkl()->exists()) {
                abort(409, 'Hasil inspeksi untuk jadwal ini sudah pernah dikirim.');
            }

            $totalSkor = collect(self::CHECKLIST_FIELDS)
                ->filter(fn (string $field): bool => (bool) ($validated[$field] ?? false))
                ->count();
            $kategoriRisiko = InspeksiIkl::hitungKategoriRisiko($totalSkor);

            $inspeksi = InspeksiIkl::create([
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

            $jadwal->update(['status_kunjungan' => 'selesai']);

            $laporan = $jadwal->laporan()->first();
            if ($laporan instanceof LaporanWarga) {
                $laporan->update(['status_laporan' => 'selesai']);
            }

            return [
                'id_inspeksi' => $inspeksi->id_inspeksi,
                'id_jadwal' => $jadwal->id_jadwal,
                'total_skor_ya' => $totalSkor,
                'kategori_risiko' => $kategoriRisiko,
                'label_kategori_risiko' => match ($kategoriRisiko) {
                    'aman' => 'Aman',
                    'risiko_sedang' => 'Sedang',
                    'risiko_tinggi' => 'Tinggi',
                },
                'status_kunjungan' => $jadwal->status_kunjungan,
                'status_laporan' => $laporan?->status_laporan,
            ];
        });

        return response()->json([
            'message' => 'Hasil inspeksi berhasil dikirim.',
            'data' => $result,
        ], 201);
    }
}
