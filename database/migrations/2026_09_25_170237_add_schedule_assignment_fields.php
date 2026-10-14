<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (Schema::getForeignKeys('jadwal_inspeksi') as $foreignKey) {
            if (in_array('id_operator', $foreignKey['columns'], true)) {
                Schema::table('jadwal_inspeksi', function (Blueprint $table) use ($foreignKey): void {
                    $table->dropForeign($foreignKey['name'] ?? $foreignKey['columns']);
                });
            }
        }

        foreach (Schema::getIndexes('jadwal_inspeksi') as $index) {
            if ($index['columns'] === ['id_operator']) {
                Schema::table('jadwal_inspeksi', function (Blueprint $table) use ($index): void {
                    $table->dropIndex($index['name']);
                });
            }
        }

        Schema::table('jadwal_inspeksi', function (Blueprint $table): void {
            $table->unsignedBigInteger('id_operator')->nullable()->change();
        });

        Schema::table('jadwal_inspeksi', function (Blueprint $table): void {
            if (! Schema::hasColumn('jadwal_inspeksi', 'waktu_kunjungan')) {
                $table->time('waktu_kunjungan')->nullable();
            }

            if (! Schema::hasColumn('jadwal_inspeksi', 'catatan')) {
                $table->text('catatan')->nullable();
            }

            $table->index('id_operator', 'jadwal_inspeksi_id_operator_index');
        });
    }

    public function down(): void
    {
        if (Schema::hasIndex('jadwal_inspeksi', 'jadwal_inspeksi_id_operator_index')) {
            Schema::table('jadwal_inspeksi', function (Blueprint $table): void {
                $table->dropIndex('jadwal_inspeksi_id_operator_index');
            });
        }

        Schema::table('jadwal_inspeksi', function (Blueprint $table): void {
            $table->integer('id_operator')->nullable()->change();
            $table->index('id_operator', 'id_operator');
        });

        $columnsToDrop = array_values(array_filter(
            ['waktu_kunjungan', 'catatan'],
            fn (string $column): bool => Schema::hasColumn('jadwal_inspeksi', $column),
        ));

        if ($columnsToDrop !== []) {
            Schema::table('jadwal_inspeksi', function (Blueprint $table) use ($columnsToDrop): void {
                $table->dropColumn($columnsToDrop);
            });
        }
    }
};
