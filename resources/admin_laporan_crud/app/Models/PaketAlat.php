<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaketAlat extends Model
{
    protected $table = 'paket_alat';
    protected $primaryKey = 'id_paket';
    public $timestamps = false;

    protected $fillable = [
        'nama_paket',
        'kategori_laporan',
        'deskripsi_alat',
    ];
}
