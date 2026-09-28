<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'tanggal_inspeksi' => 'date',
        'p1_dinding_sumur_retak' => 'boolean',
        'p2_penutup_tidak_rapat' => 'boolean',
        'p3_lantai_becek_retak' => 'boolean',
        'p4_spal_tersumbat' => 'boolean',
        'p5_air_keruh_berbau' => 'boolean',
        'total_skor_ya' => 'integer',
        'jarak_sumber_pencemar' => 'integer',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    /**
     * Jadwal kunjungan yang menghasilkan data inspeksi ini.
     */
    public function jadwal()
    {
        return $this->belongsTo(JadwalInspeksi::class, 'id_jadwal', 'id_jadwal');
    }

    // ─── Business Logic ───────────────────────────────────────────────────────

    /**
     * Hitung total skor (jumlah kondisi berbahaya yang = true)
     * dan tentukan kategori risiko berdasarkan scoring WHO/Kemenkes.
     *
     * Kategori:
     *   0     → aman
     *   1–2   → risiko_sedang
     *   3–5   → risiko_tinggi
     */
    public static function hitungKategoriRisiko(int $totalSkor): string
    {
        if ($totalSkor === 0) {
            return 'aman';
        }

        if ($totalSkor <= 2) {
            return 'risiko_sedang';
        }

        return 'risiko_tinggi';
    }

    /**
     * Accessor: label kategori risiko yang mudah dibaca (human-readable).
     */
    public function getLabelKategoriRisikoAttribute(): string
    {
        return match ($this->kategori_risiko) {
            'aman' => '✅ Aman',
            'risiko_sedang' => '⚠️ Risiko Sedang',
            'risiko_tinggi' => '🔴 Risiko Tinggi',
            default => '-',
        };
    }
}
