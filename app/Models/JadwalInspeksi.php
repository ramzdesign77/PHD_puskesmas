<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalInspeksi extends Model
{
    protected $table = 'jadwal_inspeksi';

    protected $primaryKey = 'id_jadwal';

    public $timestamps = false;

    protected $fillable = [
        'id_laporan',
        'id_operator',
        'id_paket',
        'tanggal_kunjungan',
        'jenis_kunjungan',
        'status_kunjungan',
    ];

    protected $casts = [
        'tanggal_kunjungan' => 'date',
        'created_at' => 'datetime',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    /**
     * Laporan warga yang memicu jadwal ini (nullable untuk IKL rutin).
     */
    public function laporan()
    {
        return $this->belongsTo(LaporanWarga::class, 'id_laporan', 'id_laporan');
    }

    /**
     * Petugas sanitarian yang ditugaskan (FK ke users.id_user, bukan tabel petugas).
     */
    public function operator()
    {
        return $this->belongsTo(User::class, 'id_operator', 'id_user');
    }

    /** @deprecated Gunakan operator() — relasi sekarang langsung ke tabel users. */
    public function petugas()
    {
        return $this->operator();
    }

    /**
     * Paket alat inspeksi (nullable).
     */
    public function paketAlat()
    {
        return $this->belongsTo(PaketAlat::class, 'id_paket', 'id_paket');
    }

    /**
     * Hasil inspeksi IKL Air yang dilampirkan pada jadwal ini.
     */
    public function inspeksiIkl()
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
}
