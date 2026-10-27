<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaketAlat extends Model
{
    protected $table = 'ref_paket_alat';

    protected $primaryKey = 'id_paket';

    public $timestamps = true;

    protected $fillable = [
        'nama_paket',
        'kategori_laporan',
        'deskripsi_alat',
        'updated_at',
    ];

    public function jadwalInspeksi(): HasMany
    {
        return $this->hasMany(JadwalInspeksi::class, 'id_paket', 'id_paket');
    }
}
