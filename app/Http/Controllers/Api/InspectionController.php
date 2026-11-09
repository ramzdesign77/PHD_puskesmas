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
    private const CHECKLIST_FIELDS = ['p1', 'p2', 'p3', 'p4', 'p5', 'p6', 'p7'];

    public function store(Request $request): JsonResponse
    {
        $legacyFields = [1 => 'p1_dinding_sumur_retak', 2 => 'p2_penutup_tidak_rapat', 3 => 'p3_lantai_becek_retak', 4 => 'p4_spal_tersumbat', 5 => 'p5_air_keruh_berbau'];
        foreach (range(1, 7) as $number) {
            $aliases = ['indikator_'.$number, 'p'.$number.'_indikator_'.$number];
            if (isset($legacyFields[$number])) {
                $aliases[] = $legacyFields[$number];
            }

            foreach ($aliases as $alias) {
                if ($request->exists($alias)) {
                    $request->merge(['p'.$number => $request->input($alias)]);
                    break;
                }
            }
        }

        $validated = $request->validate([
            'id_jadwal' => ['required', 'integer', 'min:1'],
            'jenis_sarana_air' => ['required', 'in:sumur_bor,sumur_terlindung,pdam,mata_air,lainnya'],
            'jarak_sumber_pencemar' => ['required', 'integer', 'min:0', 'max:99999'],
            'kategori_ikl' => ['nullable', 'in:sumur_gali,sumur_bor,sarang_nyamuk_dbd,jamban_limbah_stbm,tumpukan_sampah'],
            'p1' => ['sometimes', 'boolean'], 'p2' => ['sometimes', 'boolean'],
            'p3' => ['sometimes', 'boolean'], 'p4' => ['sometimes', 'boolean'],
            'p5' => ['sometimes', 'boolean'], 'p6' => ['sometimes', 'boolean'], 'p7' => ['sometimes', 'boolean'],
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

        $category = $validated['kategori_ikl'] ?? ($validated['jenis_sarana_air'] === 'sumur_bor' ? 'sumur_bor' : 'sumur_gali');
        $answers = [];
        foreach (self::CHECKLIST_FIELDS as $index => $field) {
            $number = $index + 1;
            $legacyField = [1 => 'p1_dinding_sumur_retak', 2 => 'p2_penutup_tidak_rapat', 3 => 'p3_lantai_becek_retak', 4 => 'p4_spal_tersumbat', 5 => 'p5_air_keruh_berbau'][$number] ?? null;
            $answers[$field] = (bool) ($validated[$field] ?? ($legacyField ? ($validated[$legacyField] ?? false) : false));
        }
        $totalSkor = count(array_filter($answers));
        $kategoriRisiko = InspeksiIkl::calculateRiskCategory($totalSkor, $category, $answers)['code'];

        $result = DB::transaction(function () use ($validated, $officer, $category, $answers, $totalSkor, $kategoriRisiko): array {
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

            $inspeksi = InspeksiIkl::create([
                'id_jadwal' => $jadwal->id_jadwal,
                'kategori_ikl' => $category,
                'jenis_sarana_air' => $validated['jenis_sarana_air'],
                'jarak_sumber_pencemar' => $validated['jarak_sumber_pencemar'],
                'p1_dinding_sumur_retak' => $answers['p1'],
                'p2_penutup_tidak_rapat' => $answers['p2'],
                'p3_lantai_becek_retak' => $answers['p3'],
                'p4_spal_tersumbat' => $answers['p4'],
                'p5_air_keruh_berbau' => $answers['p5'],
                'p1' => $answers['p1'], 'p2' => $answers['p2'], 'p3' => $answers['p3'],
                'p4' => $answers['p4'], 'p5' => $answers['p5'],
                'p6_indikator_6' => $answers['p6'], 'p7_indikator_7' => $answers['p7'],
                'detail_indikator' => InspeksiIkl::buildIndicatorDetails($category, $answers),
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
                'detail_indikator' => $inspeksi->detail_indikator,
                'label_kategori_risiko' => match ($kategoriRisiko) {
                    'aman' => 'Risiko Rendah (Aman)',
                    'risiko_sedang' => 'Risiko Sedang',
                    'risiko_tinggi' => 'Risiko Tinggi',
                    'risiko_amat_tinggi' => 'Risiko Amat Tinggi',
                    default => 'Risiko Rendah (Aman)',
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
