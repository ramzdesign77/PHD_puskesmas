<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $attributes = [
        'status' => 'Pendataan/Pending',
    ];

    protected $fillable = [
        'nama_lengkap',
        'nik',
        'nomor_telepon',
        'village_id',
        'rt',
        'rw',
        'kategori_laporan',
        'deskripsi_masalah',
        'foto_bukti',
    ];

    public function village(): BelongsTo
    {
        return $this->belongsTo(Desa::class, 'village_id', 'id_desa');
    }
}
