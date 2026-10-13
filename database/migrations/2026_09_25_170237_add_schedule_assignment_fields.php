<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

public function up(): void
{
    // Cari foreign key yang benar-benar ada pada kolom id_operator.
    $foreignKeys = DB::select("
        SELECT CONSTRAINT_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'jadwal_inspeksi'
          AND COLUMN_NAME = 'id_operator'
          AND REFERENCED_TABLE_NAME IS NOT NULL
    ");

    foreach ($foreignKeys as $foreignKey) {
        $name = str_replace('`', '``', $foreignKey->CONSTRAINT_NAME);

        DB::statement(
            "ALTER TABLE `jadwal_inspeksi` DROP FOREIGN KEY `{$name}`"
        );
    }

    // Hapus index bernama id_operator jika memang tersedia.
    $indexes = DB::select("
        SELECT INDEX_NAME
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'jadwal_inspeksi'
          AND INDEX_NAME = 'id_operator'
    ");

    if (!empty($indexes)) {
        DB::statement(
            'ALTER TABLE `jadwal_inspeksi` DROP INDEX `id_operator`'
        );
    }

    Schema::table('jadwal_inspeksi', function (Blueprint $table) {
        $table->unsignedBigInteger('id_operator')->nullable()->change();

        if (!Schema::hasColumn('jadwal_inspeksi', 'waktu_kunjungan')) {
            $table->time('waktu_kunjungan')
                ->nullable()
                ->after('tanggal_kunjungan');
        }

        if (!Schema::hasColumn('jadwal_inspeksi', 'catatan')) {
            $table->text('catatan')
                ->nullable()
                ->after('status_kunjungan');
        }

                    $indexExists = DB::selectOne("
            SELECT COUNT(*) AS total
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'jadwal_inspeksi'
            AND INDEX_NAME = 'jadwal_inspeksi_id_operator_index'
        ");

        if ((int) $indexExists->total === 0) {
            $table->index('id_operator');
        }

        $table->foreign('id_operator', 'jadwal_inspeksi_petugas_fk')
            ->references('id_petugas')
            ->on('petugas')
            ->nullOnDelete();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_inspeksi', function (Blueprint $table) {
            //
        });
    }
};
