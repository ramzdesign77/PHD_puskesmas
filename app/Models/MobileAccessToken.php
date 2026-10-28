<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobileAccessToken extends Model
{
    protected $table = 'app_mobile_tokens';

    public $timestamps = true;

    protected $fillable = [
        'id_user',
        'name',
        'token_hash',
        'created_at',
        'updated_at',
        'last_used_at',
        'expires_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
