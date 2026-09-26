<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';

    protected $primaryKey = 'id_user';

    public $timestamps = false;

    protected $fillable = [
        'nama_lengkap',
        'id_petugas',
        'username',
        'email',
        'password',
        'no_telepon',
        'role',
        'wilayah_kerja',
        'alamat',
        'foto',
        'is_active',
        'nip',
        'nik',
    ];

    protected $hidden = ['password'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'id_user';
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    /**
     * Jadwal inspeksi yang ditugaskan kepada user (petugas sanitarian).
     */
    public function jadwalInspeksi()
    {
        return $this->hasMany(JadwalInspeksi::class, 'id_operator', 'id_user');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /** Apakah user berperan sebagai sanitarian / petugas lapangan. */
    public function isSanitarian(): bool
    {
        return in_array($this->role, ['sanitarian', 'staf_backup_kluster4']);
    }

    /** Apakah user berperan sebagai admin atau kepala puskesmas. */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'kepala_puskesmas']);
    }
}
