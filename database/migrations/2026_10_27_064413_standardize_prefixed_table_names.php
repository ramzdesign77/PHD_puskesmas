<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE_RENAMES = [
        'tm_desa' => 'ref_desa',
        'tm_edukasi' => 'ref_edukasi',
        'tm_paket_alat' => 'ref_paket_alat',
        'tm_petugas' => 'app_petugas',
        'tm_users' => 'app_users',
        'tr_mobile_access_tokens' => 'app_mobile_tokens',
        'tr_laporan_warga' => 'trx_laporan_warga',
        'tr_jadwal_inspeksi' => 'trx_jadwal_inspeksi',
        'tr_inspeksi_ikl' => 'trx_inspeksi_ikl',
        'tr_log_notifikasi_wa' => 'log_notifikasi_wa',
    ];

    private const RENAME_LOG_TABLE = 'migration_table_renames';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (self::TABLE_RENAMES as $sourceTable => $targetTable) {
            if (Schema::hasTable($sourceTable) && Schema::hasTable($targetTable)) {
                throw new RuntimeException(
                    "Both {$sourceTable} and {$targetTable} exist; resolve the table-name conflict before migrating."
                );
            }
        }

        $tablesToRename = array_filter(
            self::TABLE_RENAMES,
            static fn (string $targetTable, string $sourceTable): bool => (
                Schema::hasTable($sourceTable) && ! Schema::hasTable($targetTable)
            ),
            ARRAY_FILTER_USE_BOTH,
        );

        if ($tablesToRename === [] && ! Schema::hasTable(self::RENAME_LOG_TABLE)) {
            return;
        }

        if (! Schema::hasTable(self::RENAME_LOG_TABLE)) {
            Schema::create(self::RENAME_LOG_TABLE, function (Blueprint $table): void {
                $table->id();
                $table->string('source_table')->unique();
                $table->string('target_table');
            });
        }

        Schema::disableForeignKeyConstraints();

        try {
            foreach ($tablesToRename as $sourceTable => $targetTable) {
                DB::table(self::RENAME_LOG_TABLE)->updateOrInsert(
                    ['source_table' => $sourceTable],
                    ['target_table' => $targetTable],
                );

                Schema::rename($sourceTable, $targetTable);
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
        if (! Schema::hasTable(self::RENAME_LOG_TABLE)) {
            return;
        }

        $renames = DB::table(self::RENAME_LOG_TABLE)->orderByDesc('id')->get();

        foreach ($renames as $rename) {
            $sourceExists = Schema::hasTable($rename->source_table);
            $targetExists = Schema::hasTable($rename->target_table);

            if ($sourceExists && $targetExists) {
                throw new RuntimeException(
                    "Both {$rename->source_table} and {$rename->target_table} exist; resolve the table-name conflict before rolling back."
                );
            }

            if (! $sourceExists && ! $targetExists) {
                throw new RuntimeException(
                    "Neither {$rename->source_table} nor {$rename->target_table} exists; restore the table before rolling back."
                );
            }
        }

        Schema::disableForeignKeyConstraints();

        try {
            foreach ($renames as $rename) {
                if (Schema::hasTable($rename->target_table)) {
                    Schema::rename($rename->target_table, $rename->source_table);
                }
            }

            Schema::drop(self::RENAME_LOG_TABLE);
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
};
