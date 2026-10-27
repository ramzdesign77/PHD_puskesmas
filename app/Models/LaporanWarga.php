<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LaporanWarga extends Model
{
    protected $table = 'trx_laporan_warga';

    protected $primaryKey = 'id_laporan';

    public $timestamps = true;

    protected $fillable = [
        'kode_tiket',
        'nama_pelapor',
        'nik_pelapor',
        'no_wa',
        'id_desa',
        'rt',
        'rw',
        'kategori_laporan',
        'deskripsi',
        'foto_bukti',
        'status_laporan',
        'alasan_penolakan',
        'latitude',
        'longitude',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class, 'id_desa', 'id_desa');
    }

    /**
     * Satu laporan memiliki satu jadwal inspeksi.
     * Gunakan hasOne karena satu laporan hanya memiliki satu jadwal aktif.
     */
    public function jadwal(): HasOne
    {
        return $this->hasOne(JadwalInspeksi::class, 'id_laporan', 'id_laporan')
            ->latestOfMany('id_jadwal');
    }

    public function notifikasiWa(): HasMany
    {
        return $this->hasMany(LogNotifikasiWa::class, 'id_laporan', 'id_laporan');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    /** Hanya laporan yang menunggu verifikasi admin. */
    public function scopeMenunggu(Builder $query): Builder
    {
        return $query->where('status_laporan', 'menunggu');
    }

    /** Hanya laporan yang sudah dijadwalkan. */
    public function scopeDijadwalkan(Builder $query): Builder
    {
        return $query->where('status_laporan', 'dijadwalkan');
    }

    /** Laporan yang masih aktif (menunggu atau dijadwalkan). */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->whereIn('status_laporan', ['menunggu', 'dijadwalkan', 'dibaca', 'diterima']);
    }

    /** Laporan yang sudah selesai diproses. */
    public function scopeSelesai(Builder $query): Builder
    {
        return $query->where('status_laporan', 'selesai');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Generate kode tiket unik dengan format: IKL-YYYYMMDD-XXXX
     */
    public static function generateKodeTiket(): string
    {
        $prefix = 'IKL-'.now()->format('Ymd').'-';
        $suffix = strtoupper(substr(uniqid(), -4));

        return $prefix.$suffix;
    }
}
