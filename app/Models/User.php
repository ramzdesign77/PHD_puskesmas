<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

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
        'nik',
        'nip',
        'nip_nik',
        'role',
        'roles',
        'wilayah_kerja',
        'alamat',
        'foto',
        'id_desa',
        'is_active',
    ];

    protected $hidden = ['password'];

    protected static function booted(): void
    {
        static::created(function (self $user): void {
            $id = $user->getKey();

            DB::table($user->getTable())
                ->where('id', $id)
                ->update(['id_user' => $id]);
        });
    }

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
            'roles' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'id_user';
    }

    public function roles(): array
    {
        return $this->getRolesListAttribute();
    }

    /**
     * Resolve roles from the new multi-role column, falling back to the legacy role.
     *
     * @return array<int, string>
     */
    public function getRolesListAttribute(): array
    {
        $roles = $this->getAttribute('roles');

        if (is_string($roles)) {
            $decodedRoles = json_decode($roles, true);
            $roles = is_array($decodedRoles) ? $decodedRoles : [];
        }

        if (! is_array($roles) || $roles === []) {
            $legacyRole = $this->getAttribute('role');

            if (! is_string($legacyRole) || trim($legacyRole) === '') {
                return [];
            }

            $decodedRoles = json_decode($legacyRole, true);
            $roles = is_array($decodedRoles) ? $decodedRoles : explode(',', $legacyRole);
        }

        $roles = array_map(
            static fn (mixed $role): string => is_string($role) ? trim($role) : '',
            $roles,
        );

        return array_values(array_unique(array_filter(
            $roles,
            static fn (string $role): bool => $role !== '',
        )));
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles(), true);
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
        return collect($this->roles())->intersect(['petugas', 'sanitarian', 'staf_backup_kluster4'])->isNotEmpty();
    }

    /** Apakah user berperan sebagai admin atau kepala puskesmas. */
    public function isAdmin(): bool
    {
        return collect($this->roles())->intersect(['admin', 'kepala_puskesmas'])->isNotEmpty();
    }
}
