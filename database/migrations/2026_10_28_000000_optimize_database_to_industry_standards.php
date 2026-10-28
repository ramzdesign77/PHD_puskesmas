<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            if (Schema::hasTable('reports') && DB::table('reports')->exists()) {
                throw new RuntimeException(
                    'The reports table contains data. Archive or migrate those records before dropping it.'
                );
            }

            Schema::dropIfExists('migration_table_renames');
            Schema::dropIfExists('reports');

            if (Schema::hasTable('app_users')) {
                if (Schema::hasColumn('app_users', 'id_user') && Schema::hasColumn('app_users', 'id')) {
                    if (DB::table('app_users as pending_user')
                        ->join('app_users as existing_user', 'existing_user.id_user', '=', 'pending_user.id')
                        ->whereNull('pending_user.id_user')
                        ->whereColumn('pending_user.id', '!=', 'existing_user.id')
                        ->exists()) {
                        throw new RuntimeException('Cannot backfill app_users.id_user because the resulting values would not be unique.');
                    }

                    DB::statement('UPDATE app_users SET id_user = id WHERE id_user IS NULL');
                }

                if (Schema::hasColumn('app_users', 'name') && Schema::hasColumn('app_users', 'nama_lengkap')) {
                    DB::statement('UPDATE app_users SET name = nama_lengkap WHERE name IS NULL AND nama_lengkap IS NOT NULL');
                    DB::statement('UPDATE app_users SET nama_lengkap = name WHERE nama_lengkap IS NULL AND name IS NOT NULL');
                }

                if (Schema::hasColumn('app_users', 'nip_nik') && Schema::hasColumn('app_users', 'nip')) {
                    DB::statement('UPDATE app_users SET nip_nik = nip WHERE nip_nik IS NULL AND nip IS NOT NULL');
                }

                if (Schema::hasColumn('app_users', 'nip_nik') && Schema::hasColumn('app_users', 'nik')) {
                    DB::statement('UPDATE app_users SET nip_nik = nik WHERE nip_nik IS NULL AND nik IS NOT NULL');
                }

                if (
                    Schema::hasTable('ref_desa')
                    && Schema::hasColumn('app_users', 'id_desa')
                    && DB::table('app_users')
                        ->whereNotNull('id_desa')
                        ->whereNotIn('id_desa', DB::table('ref_desa')->select('id_desa'))
                        ->exists()
                ) {
                    throw new RuntimeException('Cannot add the app_users.id_desa foreign key while orphaned desa references exist.');
                }

                Schema::table('app_users', function (Blueprint $table): void {
                    if (! Schema::hasColumn('app_users', 'id_desa')) {
                        $table->integer('id_desa')->nullable()->after('id_petugas');
                    }
                });

                if (Schema::hasColumn('app_users', 'role')) {
                    $this->addIndexIfMissing('app_users', ['role'], 'idx_app_users_role');
                }

                if (Schema::hasColumn('app_users', 'is_active')) {
                    $this->addIndexIfMissing('app_users', ['is_active'], 'idx_app_users_is_active');
                }

                if (Schema::hasColumn('app_users', 'id_desa')) {
                    $this->addIndexIfMissing('app_users', ['id_desa'], 'idx_app_users_id_desa');
                    $this->addForeignKeyIfMissing('app_users', 'id_desa', 'ref_desa', 'id_desa', 'fk_app_users_id_desa');
                }
            }

            if (Schema::hasTable('app_petugas')) {
                Schema::table('app_petugas', function (Blueprint $table): void {
                    if (Schema::hasColumn('app_petugas', 'no_telepon')) {
                        if ($this->hasValueLongerThan('app_petugas', 'no_telepon', 20)) {
                            throw new RuntimeException('Cannot shorten app_petugas.no_telepon while values longer than 20 characters exist.');
                        }

                        $table->string('no_telepon', 20)->nullable()->change();
                    }

                    if (! Schema::hasColumn('app_petugas', 'updated_at')) {
                        $table->timestamp('updated_at')->nullable()->after('created_at');
                    }
                });

                if (Schema::hasColumn('app_petugas', 'is_active')) {
                    $this->addIndexIfMissing('app_petugas', ['is_active'], 'idx_app_petugas_is_active');
                }
            }

            if (Schema::hasTable('app_mobile_tokens')) {
                Schema::table('app_mobile_tokens', function (Blueprint $table): void {
                    if (! Schema::hasColumn('app_mobile_tokens', 'last_used_at')) {
                        $table->timestamp('last_used_at')->nullable()->after('created_at');
                    }

                    if (! Schema::hasColumn('app_mobile_tokens', 'expires_at')) {
                        $table->timestamp('expires_at')->nullable()->after('last_used_at');
                    }

                    if (! Schema::hasColumn('app_mobile_tokens', 'updated_at')) {
                        $table->timestamp('updated_at')->nullable()->after('expires_at');
                    }
                });
            }

            if (Schema::hasTable('ref_desa')) {
                Schema::table('ref_desa', function (Blueprint $table): void {
                    if (! Schema::hasColumn('ref_desa', 'latitude')) {
                        $table->decimal('latitude', 10, 8)->nullable();
                    }

                    if (! Schema::hasColumn('ref_desa', 'longitude')) {
                        $table->decimal('longitude', 11, 8)->nullable();
                    }

                    if (! Schema::hasColumn('ref_desa', 'updated_at')) {
                        $table->timestamp('updated_at')->nullable();
                    }
                });
            }

            if (Schema::hasTable('ref_edukasi')) {
                if (
                    Schema::hasTable('app_users')
                    && Schema::hasColumn('ref_edukasi', 'id_author')
                    && DB::table('ref_edukasi')
                        ->whereNotNull('id_author')
                        ->whereNotIn('id_author', DB::table('app_users')->select('id'))
                        ->exists()
                ) {
                    throw new RuntimeException('Cannot add the ref_edukasi.id_author foreign key while orphaned author references exist.');
                }

                Schema::table('ref_edukasi', function (Blueprint $table): void {
                    if (! Schema::hasColumn('ref_edukasi', 'id_author')) {
                        $table->unsignedBigInteger('id_author')->nullable()->after('id');
                    }

                    if (! Schema::hasColumn('ref_edukasi', 'is_published')) {
                        $table->boolean('is_published')->default(true)->after('id_author');
                    }
                });

                if (Schema::hasTable('app_users') && Schema::hasColumn('ref_edukasi', 'id_author')) {
                    $this->addForeignKeyIfMissing('ref_edukasi', 'id_author', 'app_users', 'id', 'fk_ref_edukasi_id_author');
                }

                if (Schema::hasColumn('ref_edukasi', 'category')) {
                    $this->addIndexIfMissing('ref_edukasi', ['category'], 'idx_ref_edukasi_category');
                }

                if (Schema::hasColumn('ref_edukasi', 'is_published')) {
                    $this->addIndexIfMissing('ref_edukasi', ['is_published'], 'idx_ref_edukasi_is_published');
                }
            }


            if (Schema::hasTable('trx_laporan_warga')) {
                if (
                    Schema::hasColumn('trx_laporan_warga', 'nik_pelapor')
                    && $this->hasValueLongerThan('trx_laporan_warga', 'nik_pelapor', 16)
                ) {
                    throw new RuntimeException('Cannot shorten trx_laporan_warga.nik_pelapor while values longer than 16 characters exist.');
                }

                if (
                    Schema::hasColumn('trx_laporan_warga', 'no_wa')
                    && $this->hasValueLongerThan('trx_laporan_warga', 'no_wa', 20)
                ) {
                    throw new RuntimeException('Cannot shorten trx_laporan_warga.no_wa while values longer than 20 characters exist.');
                }

                Schema::table('trx_laporan_warga', function (Blueprint $table): void {
                    if (Schema::hasColumn('trx_laporan_warga', 'nik_pelapor')) {
                        $table->string('nik_pelapor', 16)->nullable()->change();
                    }

                    if (Schema::hasColumn('trx_laporan_warga', 'no_wa')) {
                        $table->string('no_wa', 20)->nullable()->change();
                    }

                    if (! Schema::hasColumn('trx_laporan_warga', 'latitude')) {
                        $table->decimal('latitude', 10, 8)->nullable();
                    }

                    if (! Schema::hasColumn('trx_laporan_warga', 'longitude')) {
                        $table->decimal('longitude', 11, 8)->nullable();
                    }

                    if (! Schema::hasColumn('trx_laporan_warga', 'updated_at')) {
                        $table->timestamp('updated_at')->nullable();
                    }

                });

                if (Schema::hasColumn('trx_laporan_warga', 'status_laporan') && Schema::hasColumn('trx_laporan_warga', 'created_at')) {
                    $this->addIndexIfMissing('trx_laporan_warga', ['status_laporan', 'created_at'], 'idx_trx_laporan_warga_status_created');
                }

                if (Schema::hasColumn('trx_laporan_warga', 'id_desa') && Schema::hasColumn('trx_laporan_warga', 'status_laporan')) {
                    $this->addIndexIfMissing('trx_laporan_warga', ['id_desa', 'status_laporan'], 'idx_trx_laporan_warga_desa_status');
                }
            }

            if (Schema::hasTable('trx_jadwal_inspeksi')) {
                Schema::table('trx_jadwal_inspeksi', function (Blueprint $table): void {
                    if (! Schema::hasColumn('trx_jadwal_inspeksi', 'updated_at')) {
                        $table->timestamp('updated_at')->nullable();
                    }
                });

                if (
                    Schema::hasColumn('trx_jadwal_inspeksi', 'id_operator')
                    && Schema::hasColumn('trx_jadwal_inspeksi', 'tanggal_kunjungan')
                    && Schema::hasColumn('trx_jadwal_inspeksi', 'status_kunjungan')
                ) {
                    $this->addIndexIfMissing(
                        'trx_jadwal_inspeksi',
                        ['id_operator', 'tanggal_kunjungan', 'status_kunjungan'],
                        'idx_trx_jadwal_inspeksi_operator_date_status',
                    );
                }
            }

            if (Schema::hasTable('trx_inspeksi_ikl')) {
                Schema::table('trx_inspeksi_ikl', function (Blueprint $table): void {
                    if (! Schema::hasColumn('trx_inspeksi_ikl', 'intervensi_fisik')) {
                        $table->string('intervensi_fisik', 150)->nullable();
                    }

                    if (! Schema::hasColumn('trx_inspeksi_ikl', 'catatan_intervensi')) {
                        $table->text('catatan_intervensi')->nullable();
                    }

                    if (! Schema::hasColumn('trx_inspeksi_ikl', 'foto_inspeksi')) {
                        $table->string('foto_inspeksi', 255)->nullable();
                    }

                    if (! Schema::hasColumn('trx_inspeksi_ikl', 'created_at')) {
                        $table->timestamp('created_at')->nullable();
                    }

                    if (! Schema::hasColumn('trx_inspeksi_ikl', 'updated_at')) {
                        $table->timestamp('updated_at')->nullable();
                    }

                });

                if (Schema::hasColumn('trx_inspeksi_ikl', 'kategori_risiko') && Schema::hasColumn('trx_inspeksi_ikl', 'tanggal_inspeksi')) {
                    $this->addIndexIfMissing(
                        'trx_inspeksi_ikl',
                        ['kategori_risiko', 'tanggal_inspeksi'],
                        'idx_trx_inspeksi_ikl_risiko_tanggal',
                    );
                }
            }

        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            if (Schema::hasTable('app_users')) {
                Schema::table('app_users', function (Blueprint $table): void {
                    if (Schema::hasIndex('app_users', 'idx_app_users_role')) {
                        $table->dropIndex('idx_app_users_role');
                    }

                    if (Schema::hasIndex('app_users', 'idx_app_users_is_active')) {
                        $table->dropIndex('idx_app_users_is_active');
                    }

                    if (Schema::hasIndex('app_users', 'idx_app_users_id_desa')) {
                        $table->dropIndex('idx_app_users_id_desa');
                    }

                    $foreignKeys = collect(Schema::getForeignKeys('app_users'))
                        ->first(fn (array $foreignKey): bool => ($foreignKey['columns'] ?? []) === ['id_desa']
                            && ($foreignKey['foreign_table'] ?? null) === 'ref_desa'
                            && in_array($foreignKey['name'] ?? null, [null, 'fk_app_users_id_desa'], true));

                    if ($foreignKeys !== null) {
                        $table->dropForeign($foreignKeys['name'] ?? 'fk_app_users_id_desa');
                    }
                });
            }

            if (Schema::hasTable('app_petugas')) {
                Schema::table('app_petugas', function (Blueprint $table): void {
                    if (Schema::hasIndex('app_petugas', 'idx_app_petugas_is_active')) {
                        $table->dropIndex('idx_app_petugas_is_active');
                    }

                    if (Schema::hasColumn('app_petugas', 'updated_at')) {
                        $table->dropColumn('updated_at');
                    }

                    if (Schema::hasColumn('app_petugas', 'no_telepon')) {
                        $table->string('no_telepon', 255)->nullable()->change();
                    }
                });
            }

            if (Schema::hasTable('app_mobile_tokens')) {
                Schema::table('app_mobile_tokens', function (Blueprint $table): void {
                    if (Schema::hasColumn('app_mobile_tokens', 'last_used_at')) {
                        $table->dropColumn('last_used_at');
                    }

                    if (Schema::hasColumn('app_mobile_tokens', 'expires_at')) {
                        $table->dropColumn('expires_at');
                    }

                    if (Schema::hasColumn('app_mobile_tokens', 'updated_at')) {
                        $table->dropColumn('updated_at');
                    }
                });
            }

            if (Schema::hasTable('ref_desa')) {
                Schema::table('ref_desa', function (Blueprint $table): void {
                    if (Schema::hasColumn('ref_desa', 'latitude')) {
                        $table->dropColumn('latitude');
                    }

                    if (Schema::hasColumn('ref_desa', 'longitude')) {
                        $table->dropColumn('longitude');
                    }

                    if (Schema::hasColumn('ref_desa', 'updated_at')) {
                        $table->dropColumn('updated_at');
                    }
                });
            }

            if (Schema::hasTable('ref_edukasi')) {
                Schema::table('ref_edukasi', function (Blueprint $table): void {
                    if (Schema::hasIndex('ref_edukasi', 'idx_ref_edukasi_category')) {
                        $table->dropIndex('idx_ref_edukasi_category');
                    }

                    if (Schema::hasIndex('ref_edukasi', 'idx_ref_edukasi_is_published')) {
                        $table->dropIndex('idx_ref_edukasi_is_published');
                    }

                    $foreignKeys = collect(Schema::getForeignKeys('ref_edukasi'))
                        ->first(fn (array $foreignKey): bool => ($foreignKey['columns'] ?? []) === ['id_author']
                            && ($foreignKey['foreign_table'] ?? null) === 'app_users'
                            && in_array($foreignKey['name'] ?? null, [null, 'fk_ref_edukasi_id_author'], true));

                    if ($foreignKeys !== null) {
                        $table->dropForeign($foreignKeys['name'] ?? 'fk_ref_edukasi_id_author');
                    }

                    if (Schema::hasColumn('ref_edukasi', 'id_author')) {
                        $table->dropColumn('id_author');
                    }

                    if (Schema::hasColumn('ref_edukasi', 'is_published')) {
                        $table->dropColumn('is_published');
                    }
                });
            }


            if (Schema::hasTable('trx_laporan_warga')) {
                Schema::table('trx_laporan_warga', function (Blueprint $table): void {
                    if (Schema::hasIndex('trx_laporan_warga', 'idx_trx_laporan_warga_status_created')) {
                        $table->dropIndex('idx_trx_laporan_warga_status_created');
                    }

                    if (Schema::hasIndex('trx_laporan_warga', 'idx_trx_laporan_warga_desa_status')) {
                        $table->dropIndex('idx_trx_laporan_warga_desa_status');
                    }

                    if (Schema::hasColumn('trx_laporan_warga', 'latitude')) {
                        $table->dropColumn('latitude');
                    }

                    if (Schema::hasColumn('trx_laporan_warga', 'longitude')) {
                        $table->dropColumn('longitude');
                    }

                    if (Schema::hasColumn('trx_laporan_warga', 'updated_at')) {
                        $table->dropColumn('updated_at');
                    }

                    if (Schema::hasColumn('trx_laporan_warga', 'nik_pelapor')) {
                        $table->string('nik_pelapor', 255)->nullable()->change();
                    }

                    if (Schema::hasColumn('trx_laporan_warga', 'no_wa')) {
                        $table->string('no_wa', 255)->nullable()->change();
                    }
                });
            }

            if (Schema::hasTable('trx_jadwal_inspeksi')) {
                Schema::table('trx_jadwal_inspeksi', function (Blueprint $table): void {
                    if (Schema::hasIndex('trx_jadwal_inspeksi', 'idx_trx_jadwal_inspeksi_operator_date_status')) {
                        $table->dropIndex('idx_trx_jadwal_inspeksi_operator_date_status');
                    }


                    if (Schema::hasColumn('trx_jadwal_inspeksi', 'updated_at')) {
                        $table->dropColumn('updated_at');
                    }
                });
            }

            if (Schema::hasTable('trx_inspeksi_ikl')) {
                Schema::table('trx_inspeksi_ikl', function (Blueprint $table): void {
                    if (Schema::hasIndex('trx_inspeksi_ikl', 'idx_trx_inspeksi_ikl_risiko_tanggal')) {
                        $table->dropIndex('idx_trx_inspeksi_ikl_risiko_tanggal');
                    }

                    foreach (['intervensi_fisik', 'catatan_intervensi', 'foto_inspeksi', 'created_at', 'updated_at'] as $column) {
                        if (Schema::hasColumn('trx_inspeksi_ikl', $column)) {
                            $table->dropColumn($column);
                        }
                    }
                });
            }

        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    /**
     * Add an index only when the named index is not already present.
     *
     * @param  array<int, string>  $columns
     */
    private function addIndexIfMissing(string $tableName, array $columns, string $indexName): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        $existingIndex = collect(Schema::getIndexes($tableName))->first(
            fn (array $index): bool => ($index['columns'] ?? []) === $columns,
        );

        if ($existingIndex !== null || Schema::hasIndex($tableName, $indexName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName): void {
            $table->index($columns, $indexName);
        });
    }

    private function hasValueLongerThan(string $tableName, string $columnName, int $maximumLength): bool
    {
        $lengthFunction = DB::connection()->getDriverName() === 'sqlite' ? 'LENGTH' : 'CHAR_LENGTH';

        return DB::table($tableName)
            ->whereRaw("{$lengthFunction}({$columnName}) > ?", [$maximumLength])
            ->exists();
    }

    private function addForeignKeyIfMissing(
        string $tableName,
        string $columnName,
        string $referencedTable,
        string $referencedColumn,
        string $constraintName,
    ): void {
        if (
            ! Schema::hasTable($tableName)
            || ! Schema::hasTable($referencedTable)
            || ! Schema::hasColumn($tableName, $columnName)
            || ! Schema::hasColumn($referencedTable, $referencedColumn)
        ) {
            return;
        }

        $foreignKey = collect(Schema::getForeignKeys($tableName))->first(
            fn (array $key): bool => ($key['columns'] ?? []) === [$columnName]
                && ($key['foreign_table'] ?? null) === $referencedTable
                && ($key['foreign_columns'] ?? []) === [$referencedColumn],
        );

        if ($foreignKey !== null) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use (
            $columnName,
            $referencedTable,
            $referencedColumn,
            $constraintName,
        ): void {
            $table->foreign($columnName, $constraintName)
                ->references($referencedColumn)
                ->on($referencedTable)
                ->nullOnDelete();
        });
    }
};
