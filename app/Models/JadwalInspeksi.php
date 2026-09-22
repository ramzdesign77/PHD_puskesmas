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
        'id_operator', // dipakai sebagai referensi ke petugas.id_petugas
        'id_paket',
        'tanggal_kunjungan',
        'jenis_kunjungan',
        'status_kunjungan',
    ];

    public function laporan()
    {
        return $this->belongsTo(LaporanWarga::class, 'id_laporan', 'id_laporan');
    }

    public function petugas()
    {
        return $this->belongsTo(Petugas::class, 'id_operator', 'id_petugas');
    }

    public function paketAlat()
    {
        return $this->belongsTo(PaketAlat::class, 'id_paket', 'id_paket');
    }
}
