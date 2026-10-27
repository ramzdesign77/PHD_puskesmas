<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JadwalInspeksi extends Model
{
    protected $table = 'trx_jadwal_inspeksi';

    protected $primaryKey = 'id_jadwal';

    public $timestamps = true;

    protected $fillable = [
        'id_laporan',
        'id_operator',
        'tanggal_kunjungan',
        'jam_mulai',
        'jenis_kunjungan',
        'status_kunjungan',
        'updated_at',
    ];

    protected $casts = [
        'tanggal_kunjungan' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    /**
     * Laporan warga yang memicu jadwal ini (nullable untuk IKL rutin).
     */
    public function laporan(): BelongsTo
    {
        return $this->belongsTo(LaporanWarga::class, 'id_laporan', 'id_laporan');
    }

    /**
     * Petugas sanitarian yang ditugaskan (FK ke app_users.id_user, bukan app_petugas).
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_operator', 'id_user');
    }

    /** @deprecated Gunakan operator() — relasi sekarang langsung ke app_users. */
    public function petugas(): BelongsTo
    {
        return $this->operator();
    }

    /**
     * Paket alat inspeksi (nullable).
     */
    public function paketAlat(): BelongsTo
    {
        return $this->belongsTo(PaketAlat::class, 'id_paket', 'id_paket');
    }

    /**
     * Hasil inspeksi IKL Air yang dilampirkan pada jadwal ini.
     */
    public function inspeksiIkl(): HasOne
    {
        return $this->hasOne(InspeksiIkl::class, 'id_jadwal', 'id_jadwal');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeTerjadwal($query)
    {
        return $query->where('status_kunjungan', 'terjadwal');
    }

    public function scopeSelesai($query)
    {
        return $query->where('status_kunjungan', 'selesai');
    }

    public function scopeForOperatorOnDate(
        Builder $query,
        int|string $idOperator,
        string $tanggalKunjungan,
        ?string $jam = null,
        ?int $exceptId = null
    ): Builder {
        $query->where('id_operator', $idOperator)
            ->whereDate('tanggal_kunjungan', $tanggalKunjungan)
            ->where('status_kunjungan', 'terjadwal');

        if ($jam !== null) {
            $query->where('jam_mulai', $jam);
        }

        if ($exceptId !== null) {
            $query->where('id_jadwal', '!=', $exceptId);
        }

        return $query;
    }
}
