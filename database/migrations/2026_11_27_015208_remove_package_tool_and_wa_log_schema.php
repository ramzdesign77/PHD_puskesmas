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
        if (Schema::hasTable('trx_jadwal_inspeksi') && Schema::hasColumn('trx_jadwal_inspeksi', 'id_paket')) {
            foreach (Schema::getForeignKeys('trx_jadwal_inspeksi') as $foreignKey) {
                if (($foreignKey['columns'] ?? []) !== ['id_paket']) {
                    continue;
                }

                Schema::table('trx_jadwal_inspeksi', function (Blueprint $table) use ($foreignKey): void {
                    $table->dropForeign(
                        DB::getDriverName() === 'sqlite' ? $foreignKey['columns'] : $foreignKey['name'],
                    );
                });
            }

            Schema::table('trx_jadwal_inspeksi', function (Blueprint $table): void {
                $table->dropColumn('id_paket');
            });
        }

        Schema::dropIfExists('log_notifikasi_wa');
        Schema::dropIfExists('ref_paket_alat');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        throw new RuntimeException('Deleted package and WhatsApp log data cannot be restored automatically.');
    }
};
