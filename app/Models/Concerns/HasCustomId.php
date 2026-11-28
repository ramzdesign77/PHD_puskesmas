<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use RuntimeException;

trait HasCustomId
{
    public function initializeHasCustomId(): void
    {
        $this->incrementing = false;
        $this->keyType = 'string';
    }

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }

    protected static function bootHasCustomId(): void
    {
        static::creating(function (Model $model): void {
            if (filled($model->getAttribute($model->getKeyName()))) {
                return;
            }

            for ($attempt = 0; $attempt < 10; $attempt++) {
                $candidate = static::generateCustomId($model);

                if (! $model->newQuery()->where($model->getKeyName(), $candidate)->exists()) {
                    $model->setAttribute($model->getKeyName(), $candidate);

                    return;
                }
            }

            throw new RuntimeException("Unable to generate a unique ID for {$model->getTable()}.");
        });
    }

    private static function generateCustomId(Model $model): string
    {
        $random = static fn (int $length): string => Str::upper(Str::random($length));
        $initials = static function (?string $name): string {
            $words = preg_split('/[^\p{L}\p{N}]+/u', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $letters = collect($words)->map(static fn (string $word): string => Str::upper(Str::substr(Str::ascii($word), 0, 1)))->implode('');
            $remaining = collect($words)
                ->map(static fn (string $word): string => Str::upper(Str::substr(Str::ascii($word), 1)))
                ->implode('');

            while (Str::length($letters) < 3 && $remaining !== '') {
                $letters .= Str::substr($remaining, 0, 1);
                $remaining = Str::substr($remaining, 1);
            }

            return Str::substr(str_pad($letters, 3, 'X'), 0, 3);
        };
        $datedId = static fn (string $prefix): string => $prefix.'-'.now()->format('ymd').'-'.$random(6);

        return match ($model->getTable()) {
            'app_users' => 'USR-'.$initials($model->getAttribute('nama_lengkap') ?? $model->getAttribute('name')).'-'.$random(5),
            'app_mobile_tokens' => 'TKN-'.$random(8),
            'ref_desa' => 'DSA-'.$initials($model->getAttribute('nama_desa')).'-'.$random(4),
            'ref_edukasi' => 'EDU-'.$random(8),
            'trx_laporan_warga' => $datedId('LPR'),
            'trx_jadwal_inspeksi' => $datedId('JDW'),
            'trx_inspeksi_ikl' => $datedId('IKL'),
            default => throw new RuntimeException("No custom ID format is configured for {$model->getTable()}.")
        };
    }
}
