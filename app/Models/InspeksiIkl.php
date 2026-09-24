<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspeksiIkl extends Model
{
    protected $table = 'inspeksi_ikl';

    protected $primaryKey = 'id_inspeksi';

    public $timestamps = false;

    protected $fillable = [
        'id_jadwal',
        'jenis_sarana_air',
        'jarak_sumber_pencemar',
        'p1_dinding_sumur_retak',
        'p2_penutup_tidak_rapat',
        'p3_lantai_becek_retak',
        'p4_spal_tersumbat',
        'p5_air_keruh_berbau',
        'total_skor_ya',
        'kategori_risiko',
        'rekomendasi_sanitarian',
        'tanggal_inspeksi',
    ];

    protected $casts = [
        'jarak_sumber_pencemar' => 'integer',
        'p1_dinding_sumur_retak' => 'boolean',
        'p2_penutup_tidak_rapat' => 'boolean',
        'p3_lantai_becek_retak' => 'boolean',
        'p4_spal_tersumbat' => 'boolean',
        'p5_air_keruh_berbau' => 'boolean',
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
        return match (true) {
            $totalSkorYa === 0 => 'aman',
            $totalSkorYa <= 2 => 'risiko_sedang',
            default => 'risiko_tinggi',
        };
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(JadwalInspeksi::class, 'id_jadwal', 'id_jadwal');
    }
}
