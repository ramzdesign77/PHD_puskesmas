<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'app_users';

    protected $primaryKey = 'id_user';

    public $timestamps = true;

    protected $fillable = [
        'nama_lengkap',
        'name',
        'id_petugas',
        'username',
        'email',
        'password',
        'no_telepon',
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
        static::creating(function (self $user): void {
            $user->synchronizeNames();
        });

        static::saving(function (self $user): void {
            $user->synchronizeNames();

            $roles = $user->roles;
            $legacyRole = $user->role;

            if ($user->isDirty('role') && ! $user->isDirty('roles')) {
                $decodedLegacyRole = is_string($legacyRole) ? json_decode($legacyRole, true) : null;
                $roles = is_array($decodedLegacyRole) ? $decodedLegacyRole : explode(',', (string) $legacyRole);
            }

            if (is_string($roles) && trim($roles) !== '') {
                $decodedRoles = json_decode($roles, true);
                $roles = is_array($decodedRoles) ? $decodedRoles : explode(',', $roles);
            }

            if (is_string($legacyRole) && trim($legacyRole) !== '' && (! is_array($roles) || $roles === [])) {
                $decodedLegacyRole = json_decode($legacyRole, true);
                $roles = is_array($decodedLegacyRole) ? $decodedLegacyRole : explode(',', $legacyRole);
            }

            $roles = is_array($roles) ? array_values(array_unique(array_filter(array_map(
                static fn ($role): string => is_string($role) ? trim($role) : '',
                $roles,
            ), static fn (string $role): bool => $role !== ''))) : [];

            if ($roles !== []) {
                $user->roles = $roles;
                $user->role = implode(',', $roles);
            } elseif (is_string($legacyRole) && trim($legacyRole) !== '') {
                $user->roles = [trim($legacyRole)];
                $user->role = trim($legacyRole);
            } else {
                $user->roles = [];
                $user->role = '';
            }
        });

        static::created(function (self $user): void {
            $userId = $user->getKey();

            if ($userId !== null) {
                $updated = DB::table($user->getTable())
                    ->where('id', $userId)
                    ->whereNull('id_user')
                    ->update(['id_user' => $userId]);

                if ($updated > 0) {
                    $user->setAttribute('id_user', $userId);
                    $user->syncOriginalAttribute('id_user', $userId);
                }
            }
        });
    }

    private function synchronizeNames(): void
    {
        if (blank($this->name) && filled($this->nama_lengkap)) {
            $this->name = $this->nama_lengkap;
        }

        if (blank($this->nama_lengkap) && filled($this->name)) {
            $this->nama_lengkap = $this->name;
        }

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

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class, 'id_desa', 'id_desa');
    }

    public function petugasProfile(): BelongsTo
    {
        return $this->belongsTo(Petugas::class, 'id_petugas', 'id_petugas');
    }

    public function mobileTokens(): HasMany
    {
        return $this->hasMany(MobileAccessToken::class, 'id_user', 'id_user');
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
    public function jadwalInspeksi(): HasMany
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
