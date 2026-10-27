<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Desa extends Model
{
    protected $table = 'ref_desa';

    protected $primaryKey = 'id_desa';

    public $timestamps = true;

    protected $fillable = [
        'nama_desa',
        'kode_pos',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];
}
