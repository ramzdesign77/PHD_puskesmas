<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanWarga extends Model
{
    protected $table = 'laporan_warga';
    protected $primaryKey = 'id_laporan';

    // Matikan updated_at karena di tabel hanya ada created_at
    public $timestamps = false;

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
    ];

    public function desa()
    {
        return $this->belongsTo(Desa::class, 'id_desa', 'id_desa');
    }

    public function jadwal()
    {
        return $this->hasOne(JadwalInspeksi::class, 'id_laporan', 'id_laporan');
    }
}
