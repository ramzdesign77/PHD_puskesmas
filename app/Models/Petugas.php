<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Petugas extends Model
{
    protected $table = 'app_petugas';

    protected $primaryKey = 'id_petugas';

    public $timestamps = true;

    protected $fillable = [
        'nama_petugas',
        'jabatan',
        'no_telepon',
        'is_active',
        'updated_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
