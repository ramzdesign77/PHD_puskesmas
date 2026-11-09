<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspeksiIkl extends Model
{
    /** @var array<string, array<int, string>> */
    private const QUESTIONS_BY_CATEGORY = [
        'sumur_gali' => [
            'Apakah dinding sumur gali retak, berlubang, atau tidak diplester rapat?',
            'Apakah bibir sumur tidak cukup tinggi atau rusak?',
            'Apakah lantai kedap air di sekeliling sumur tidak ada atau rusak?',
            'Apakah saluran pembuangan air sumur tersumbat atau tidak tersedia?',
            'Apakah sumur tidak memiliki penutup yang rapat?',
            'Apakah terdapat sumber pencemar kurang dari 10 meter dari sumur?',
            'Apakah air sumur tampak keruh, berwarna, atau berbau?',
        ],
        'sumur_bor' => [
            'Apakah kepala sumur bor tidak tertutup atau tidak terlindung rapat?',
            'Apakah pipa casing sumur bor retak, bocor, atau tidak tersegel?',
            'Apakah lantai kedap air di sekitar kepala sumur tidak ada atau rusak?',
            'Apakah saluran pembuangan di sekitar pompa tersumbat atau tidak ada?',
            'Apakah sambungan pipa atau instalasi pompa mengalami kebocoran?',
            'Apakah terdapat sumber pencemar kurang dari 10 meter dari sumur bor?',
            'Apakah air keluar tampak keruh, berwarna, atau berbau?',
        ],
        'sarang_nyamuk_dbd' => [
            'Apakah ditemukan tempat penampungan air terbuka di dalam rumah?',
            'Apakah tempat penampungan air tidak dikuras sekurang-kurangnya sekali seminggu?',
            'Apakah ditemukan jentik nyamuk pada tempat penampungan air?',
            'Apakah terdapat barang bekas yang dapat menampung air hujan?',
            'Apakah talang atau saluran air berpotensi menahan genangan?',
            'Apakah ditemukan wadah atau pot tanaman berisi air yang tidak terawat?',
            'Apakah terdapat genangan air di sekitar rumah yang berpotensi menjadi tempat berkembang biak nyamuk?',
        ],
        'jamban_limbah_stbm' => [
            'Apakah jamban tidak memiliki leher angsa atau pengaman tinja?',
            'Apakah tinja berpotensi mencemari permukaan tanah atau sumber air?',
            'Apakah tangki septik atau penampungan tinja bocor atau terbuka?',
            'Apakah saluran air limbah rumah tangga terbuka atau bocor?',
            'Apakah air limbah dibuang langsung ke drainase atau badan air?',
            'Apakah ditemukan genangan atau bau dari pembuangan air limbah?',
            'Apakah jamban dan sarana pengelolaan limbah tidak terawat atau tidak dapat digunakan?',
        ],
        'tumpukan_sampah' => [
            'Apakah sampah rumah tangga tidak dipilah atau dibiarkan terbuka?',
            'Apakah terdapat tumpukan sampah yang tidak diangkut secara rutin?',
            'Apakah sampah dibuang atau dibakar di tempat terbuka?',
            'Apakah ditemukan sampah yang menyumbat saluran drainase?',
            'Apakah terdapat sampah organik membusuk yang menimbulkan bau atau mengundang vektor?',
            'Apakah tempat penampungan sampah tidak tertutup atau meluap?',
            'Apakah sampah berada dekat dengan sumber air atau area pengolahan makanan?',
        ],
    ];

    protected $table = 'trx_inspeksi_ikl';

    protected $primaryKey = 'id_inspeksi';

    public $timestamps = true;

    protected $fillable = [
        'id_jadwal',
        'jenis_sarana_air',
        'kategori_ikl',
        'jarak_sumber_pencemar',
        'p1_dinding_sumur_retak',
        'p2_penutup_tidak_rapat',
        'p3_lantai_becek_retak',
        'p4_spal_tersumbat',
        'p5_air_keruh_berbau',
        'p1',
        'p2',
        'p3',
        'p4',
        'p5',
        'p6_indikator_6',
        'p7_indikator_7',
        'detail_indikator',
        'total_skor_ya',
        'kategori_risiko',
        'rekomendasi_sanitarian',
        'tanggal_inspeksi',
        'intervensi_fisik',
        'catatan_intervensi',
        'catatan_petugas',
        'foto_inspeksi',
        'foto_bukti',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'jarak_sumber_pencemar' => 'integer',
        'tanggal_inspeksi' => 'date',
        'p1_dinding_sumur_retak' => 'boolean',
        'p2_penutup_tidak_rapat' => 'boolean',
        'p3_lantai_becek_retak' => 'boolean',
        'p4_spal_tersumbat' => 'boolean',
        'p5_air_keruh_berbau' => 'boolean',
        'p1' => 'boolean',
        'p2' => 'boolean',
        'p3' => 'boolean',
        'p4' => 'boolean',
        'p5' => 'boolean',
        'p6_indikator_6' => 'boolean',
        'p7_indikator_7' => 'boolean',
        'detail_indikator' => 'array',
        'total_skor_ya' => 'integer',
    ];

    /**
     * Label bahasa Indonesia untuk setiap parameter checklist.
     *
     * @return array<string, string>
     */
    public static function labelParameter(): array
    {
        return [
            'p1_dinding_sumur_retak' => 'Dinding sumur retak / tidak diplester',
            'p2_penutup_tidak_rapat' => 'Tutup sumur tidak ada / tidak rapat',
            'p3_lantai_becek_retak' => 'Lantai sumur becek / retak / tidak ada',
            'p4_spal_tersumbat' => 'SPAL tersumbat / tidak ada',
            'p5_air_keruh_berbau' => 'Air keruh / berbau / berasa',
        ];
    }

    /**
     * Hitung kategori risiko berdasarkan total skor (jumlah jawaban "Ya").
     * Aman: 0 | Risiko Sedang: 1–2 | Risiko Tinggi: 3–5
     */
    public static function hitungKategoriRisiko(int $totalSkorYa): string
    {
        return self::calculateRiskCategory($totalSkorYa)['code'];
    }

    /** @return array<int, string> */
    public static function getQuestionsByCategory(string $category): array
    {
        return self::QUESTIONS_BY_CATEGORY[$category] ?? self::QUESTIONS_BY_CATEGORY['sumur_gali'];
    }

    /**
     * @param  array<string|int, mixed>  $answers
     * @return array{code: string, label: string}
     */
    public static function calculateRiskCategory(int $totalSkorYa, ?string $category = null, array $answers = []): array
    {
        $indicatorSeven = $answers['p7']
            ?? $answers['indikator_7']
            ?? $answers['p7_indikator_7']
            ?? $answers[7]
            ?? false;

        if ($category === 'sumur_bor' && filter_var($indicatorSeven, FILTER_VALIDATE_BOOLEAN)) {
            return ['code' => 'risiko_amat_tinggi', 'label' => 'Risiko Amat Tinggi'];
        }

        return match (true) {
            $totalSkorYa <= 1 => ['code' => 'aman', 'label' => 'Risiko Rendah (Aman)'],
            $totalSkorYa <= 3 => ['code' => 'risiko_sedang', 'label' => 'Risiko Sedang'],
            $totalSkorYa <= 5 => ['code' => 'risiko_tinggi', 'label' => 'Risiko Tinggi'],
            default => ['code' => 'risiko_amat_tinggi', 'label' => 'Risiko Amat Tinggi'],
        };
    }

    /** @return array<int, array{nomor: int, pertanyaan: string, jawaban: bool, skor: int}> */
    public static function buildIndicatorDetails(string $category, array $answers): array
    {
        $questions = self::getQuestionsByCategory($category);

        return collect($questions)->map(function (string $question, int $index) use ($answers): array {
            $number = $index + 1;
            $answer = $answers['p'.$number]
                ?? $answers['indikator_'.$number]
                ?? $answers['p'.$number.'_indikator_'.$number]
                ?? $answers[$number]
                ?? false;
            $yes = filter_var($answer, FILTER_VALIDATE_BOOLEAN);

            return ['nomor' => $number, 'pertanyaan' => $question, 'jawaban' => $yes, 'skor' => (int) $yes];
        })->all();
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(JadwalInspeksi::class, 'id_jadwal', 'id_jadwal');
    }

    /**
     * Accessor: label kategori risiko yang mudah dibaca (human-readable).
     */
    public function getLabelKategoriRisikoAttribute(): string
    {
        return match ($this->kategori_risiko) {
            'aman' => 'Risiko Rendah (Aman)',
            'risiko_sedang' => 'Risiko Sedang',
            'risiko_tinggi' => 'Risiko Tinggi',
            'risiko_amat_tinggi' => 'Risiko Amat Tinggi',
            default => '-',
        };
    }
}
