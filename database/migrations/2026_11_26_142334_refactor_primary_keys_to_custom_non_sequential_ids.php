<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            $maps = $this->buildMappings();
            $this->assertReferencesCanBeMapped($maps);
            $this->dropExistingForeignKeys();
            $this->changeForeignKeyColumns();
            $this->updateForeignKeyValues($maps);
            $this->convertPrimaryKeys($maps);
            $this->restoreForeignKeys();
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    public function down(): void
    {
        throw new RuntimeException('Custom primary keys cannot be converted back to sequential IDs automatically.');
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function buildMappings(): array
    {
        $primaryKeys = [
            'app_users' => 'id',
            'app_mobile_tokens' => 'id',
            'ref_desa' => 'id_desa',
            'ref_edukasi' => 'id',
            'trx_laporan_warga' => 'id_laporan',
            'trx_jadwal_inspeksi' => 'id_jadwal',
            'trx_inspeksi_ikl' => 'id_inspeksi',
        ];
        $maps = [];

        foreach ($primaryKeys as $table => $primaryKey) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $primaryKey)) {
                continue;
            }

            $maps[$table] = [];
            $reserved = [];
            $existingIds = DB::table($table)
                ->pluck($primaryKey)
                ->mapWithKeys(static fn ($id): array => [(string) $id => true])
                ->all();

            foreach (DB::table($table)->get() as $record) {
                $oldId = (string) $record->{$primaryKey};
                $newId = $this->newId($table, $record);

                while (isset($reserved[$newId]) || isset($existingIds[$newId])) {
                    $newId = $this->newId($table, $record);
                }

                $reserved[$newId] = true;
                $maps[$table][$oldId] = $newId;

                if ($table === 'app_users') {
                    $idUser = $record->id_user ?? null;

                    if ($idUser !== null) {
                        $maps['app_users_by_id_user'][(string) $idUser] = $newId;
                    }
                }
            }
        }

        return $maps;
    }

    private function newId(string $table, object $record): string
    {
        $random = static fn (int $length): string => Str::upper(Str::random($length));
        $initials = static function (?string $name): string {
            $words = preg_split('/[^\p{L}\p{N}]+/u', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $letters = collect($words)
                ->map(static fn (string $word): string => Str::upper(Str::substr(Str::ascii($word), 0, 1)))
                ->implode('');
            $remaining = collect($words)
                ->map(static fn (string $word): string => Str::upper(Str::substr(Str::ascii($word), 1)))
                ->implode('');

            while (Str::length($letters) < 3 && $remaining !== '') {
                $letters .= Str::substr($remaining, 0, 1);
                $remaining = Str::substr($remaining, 1);
            }

            return Str::substr(str_pad($letters, 3, 'X'), 0, 3);
        };
        $datedId = static function (string $prefix, object $row) use ($random): string {
            $date = isset($row->created_at) && $row->created_at !== null
                ? date('ymd', strtotime((string) $row->created_at))
                : now()->format('ymd');

            return $prefix.'-'.$date.'-'.$random(6);
        };

        return match ($table) {
            'app_users' => 'USR-'.$initials($record->nama_lengkap ?? $record->name ?? null).'-'.$random(5),
            'app_mobile_tokens' => 'TKN-'.$random(8),
            'ref_desa' => 'DSA-'.$initials($record->nama_desa ?? null).'-'.$random(4),
            'ref_edukasi' => 'EDU-'.$random(8),
            'trx_laporan_warga' => $datedId('LPR', $record),
            'trx_jadwal_inspeksi' => $datedId('JDW', $record),
            'trx_inspeksi_ikl' => $datedId('IKL', $record),
            default => throw new RuntimeException("No custom ID format is configured for {$table}."),
        };
    }

    /**
     * @param  array<string, array<string, string>>  $maps
     */
    private function assertReferencesCanBeMapped(array $maps): void
    {
        $references = [
            ['app_users', 'id_desa', 'ref_desa'],
            ['app_mobile_tokens', 'id_user', 'app_users_by_id_user'],
            ['ref_edukasi', 'id_author', 'app_users'],
            ['trx_laporan_warga', 'id_user', 'app_users'],
            ['trx_laporan_warga', 'id_desa', 'ref_desa'],
            ['trx_jadwal_inspeksi', 'id_laporan', 'trx_laporan_warga'],
            ['trx_jadwal_inspeksi', 'id_operator', 'app_users_by_id_user'],
            ['trx_inspeksi_ikl', 'id_jadwal', 'trx_jadwal_inspeksi'],
        ];

        foreach ($references as [$table, $column, $parentMap]) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            foreach (DB::table($table)->whereNotNull($column)->pluck($column) as $value) {
                if (! isset($maps[$parentMap][(string) $value])) {
                    throw new RuntimeException("Cannot convert {$table}.{$column}; value {$value} has no matching parent record.");
                }
            }
        }
    }

    private function dropExistingForeignKeys(): void
    {
        foreach ([
            'app_users', 'app_mobile_tokens', 'ref_edukasi', 'trx_laporan_warga',
            'trx_jadwal_inspeksi', 'trx_inspeksi_ikl',
        ] as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            foreach (Schema::getForeignKeys($tableName) as $foreignKey) {
                Schema::table($tableName, function (Blueprint $table) use ($foreignKey): void {
                    $table->dropForeign(
                        DB::getDriverName() === 'sqlite' ? $foreignKey['columns'] : $foreignKey['name'],
                    );
                });
            }
        }
    }

    private function changeForeignKeyColumns(): void
    {
        foreach ([
            ['app_users', 'id_desa', true],
            ['app_mobile_tokens', 'id_user', false],
            ['ref_edukasi', 'id_author', true],
            ['trx_laporan_warga', 'id_user', true],
            ['trx_laporan_warga', 'id_desa', true],
            ['trx_jadwal_inspeksi', 'id_laporan', true],
            ['trx_jadwal_inspeksi', 'id_operator', true],
            ['trx_inspeksi_ikl', 'id_jadwal', false],
        ] as [$tableName, $column, $nullable]) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, $column)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($column, $nullable): void {
                $definition = $table->string($column, 30);

                if ($nullable) {
                    $definition->nullable();
                }

                $definition->change();
            });
        }
    }

    /**
     * @param  array<string, array<string, string>>  $maps
     */
    private function updateForeignKeyValues(array $maps): void
    {
        foreach ([
            ['app_users', 'id_desa', 'ref_desa'],
            ['app_mobile_tokens', 'id_user', 'app_users_by_id_user'],
            ['ref_edukasi', 'id_author', 'app_users'],
            ['trx_laporan_warga', 'id_user', 'app_users'],
            ['trx_laporan_warga', 'id_desa', 'ref_desa'],
            ['trx_jadwal_inspeksi', 'id_laporan', 'trx_laporan_warga'],
            ['trx_jadwal_inspeksi', 'id_operator', 'app_users_by_id_user'],
            ['trx_inspeksi_ikl', 'id_jadwal', 'trx_jadwal_inspeksi'],
        ] as [$tableName, $column, $parentMap]) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, $column)) {
                continue;
            }

            foreach ($maps[$parentMap] ?? [] as $oldId => $newId) {
                $this->updateWhereExactId($tableName, $column, $oldId, [$column => $newId]);
            }
        }
    }

    /**
     * Update a legacy numeric ID without MySQL coercing unrelated text IDs to numbers.
     *
     * @param  array<string, string>  $values
     */
    private function updateWhereExactId(string $tableName, string $column, string $id, array $values): void
    {
        $query = DB::table($tableName);

        if (DB::getDriverName() === 'mysql') {
            $query->whereRaw('BINARY '.$query->getGrammar()->wrap($column).' = ?', [$id]);
        } else {
            $query->where($column, $id);
        }

        $query->update($values);
    }

    /**
     * @param  array<string, array<string, string>>  $maps
     */
    private function convertPrimaryKeys(array $maps): void
    {
        foreach ([
            'app_mobile_tokens' => ['id', 'id_token'],
            'ref_edukasi' => ['id', 'id_edukasi'],
        ] as $tableName => [$oldColumn, $newColumn]) {
            if (Schema::hasColumn($tableName, $oldColumn)) {
                Schema::table($tableName, function (Blueprint $table) use ($oldColumn, $newColumn): void {
                    $table->renameColumn($oldColumn, $newColumn);
                });
            }
        }

        foreach ([
            'app_mobile_tokens' => 'id_token',
            'ref_desa' => 'id_desa',
            'ref_edukasi' => 'id_edukasi',
            'trx_laporan_warga' => 'id_laporan',
            'trx_jadwal_inspeksi' => 'id_jadwal',
            'trx_inspeksi_ikl' => 'id_inspeksi',
        ] as $tableName => $primaryKey) {
            $this->changePrimaryKey($tableName, $primaryKey);
        }

        Schema::table('app_users', function (Blueprint $table): void {
            $table->string('id_user', 30)->nullable()->change();
        });

        foreach ($maps as $tableName => $mapping) {
            if ($tableName === 'app_users_by_id_user') {
                continue;
            }

            if ($tableName === 'app_users') {
                foreach ($mapping as $oldId => $newId) {
                    $this->updateWhereExactId($tableName, 'id', $oldId, ['id_user' => $newId]);
                }

                $this->changeUserPrimaryKey();

                continue;
            }

            $primaryKey = match ($tableName) {
                'app_mobile_tokens' => 'id_token',
                'ref_desa' => 'id_desa',
                'ref_edukasi' => 'id_edukasi',
                'trx_laporan_warga' => 'id_laporan',
                'trx_jadwal_inspeksi' => 'id_jadwal',
                'trx_inspeksi_ikl' => 'id_inspeksi',
                default => throw new RuntimeException("No primary key is configured for {$tableName}."),
            };

            foreach ($mapping as $oldId => $newId) {
                $this->updateWhereExactId($tableName, $primaryKey, $oldId, [$primaryKey => $newId]);
            }
        }
    }

    private function changePrimaryKey(string $tableName, string $column): void
    {
        if (DB::getDriverName() === 'mysql') {
            Schema::table($tableName, function (Blueprint $table) use ($column): void {
                $table->string($column, 30)->change();
            });

            Schema::table($tableName, function (Blueprint $table) use ($column): void {
                $table->dropPrimary();
                $table->primary($column);
            });

            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($column): void {
            $table->dropPrimary();
            $table->string($column, 30)->change();
            $table->primary($column);
        });
    }

    private function changeUserPrimaryKey(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('app_users', function (Blueprint $table): void {
                $table->renameColumn('id', 'legacy_user_id');
            });

            Schema::table('app_users', function (Blueprint $table): void {
                $table->dropPrimary();

                foreach (Schema::getIndexes('app_users') as $index) {
                    if (($index['columns'] ?? []) === ['id_user'] && ($index['unique'] ?? false)) {
                        $table->dropUnique($index['name']);
                    }
                }

                $table->string('legacy_user_id', 30)->change();
                $table->string('id_user', 30)->nullable(false)->change();
                $table->primary('id_user');
            });

            Schema::table('app_users', function (Blueprint $table): void {
                $table->dropColumn('legacy_user_id');
            });

            return;
        }

        Schema::table('app_users', function (Blueprint $table): void {
            $table->string('id', 30)->change();
        });

        Schema::table('app_users', function (Blueprint $table): void {
            foreach (Schema::getIndexes('app_users') as $index) {
                if (($index['columns'] ?? []) === ['id_user'] && ($index['unique'] ?? false) && ($index['name'] ?? null) !== 'PRIMARY') {
                    $table->dropUnique($index['name']);
                }
            }
        });

        Schema::table('app_users', function (Blueprint $table): void {
            $table->dropPrimary();
            $table->dropColumn('id');
            $table->string('id_user', 30)->nullable(false)->change();
            $table->primary('id_user');
        });
    }

    private function restoreForeignKeys(): void
    {
        foreach ([
            ['app_users', 'id_desa', 'ref_desa', 'id_desa', 'set null'],
            ['app_mobile_tokens', 'id_user', 'app_users', 'id_user', 'cascade'],
            ['ref_edukasi', 'id_author', 'app_users', 'id_user', 'set null'],
            ['trx_laporan_warga', 'id_desa', 'ref_desa', 'id_desa', 'set null'],
            ['trx_laporan_warga', 'id_user', 'app_users', 'id_user', 'set null'],
            ['trx_jadwal_inspeksi', 'id_laporan', 'trx_laporan_warga', 'id_laporan', 'cascade'],
            ['trx_jadwal_inspeksi', 'id_operator', 'app_users', 'id_user', 'set null'],
            ['trx_inspeksi_ikl', 'id_jadwal', 'trx_jadwal_inspeksi', 'id_jadwal', 'cascade'],
        ] as [$tableName, $column, $parentTable, $parentColumn, $onDelete]) {
            Schema::table($tableName, function (Blueprint $table) use ($column, $parentTable, $parentColumn, $onDelete): void {
                $foreign = $table->foreign($column)
                    ->references($parentColumn)
                    ->on($parentTable);

                if ($onDelete === 'cascade') {
                    $foreign->cascadeOnDelete();
                } else {
                    $foreign->nullOnDelete();
                }
            });
        }
    }
};
