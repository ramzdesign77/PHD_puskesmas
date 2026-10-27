<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogNotifikasiWa extends Model
{
    protected $table = 'log_notifikasi_wa';

    protected $primaryKey = 'id_log';

    public $timestamps = false;

    protected $fillable = [
        'id_laporan',
        'no_wa_tujuan',
        'pesan_terkirim',
        'status_kirim',
        'sent_at',
        'response_gateway',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function laporan(): BelongsTo
    {
        return $this->belongsTo(LaporanWarga::class, 'id_laporan', 'id_laporan');
    }
}
