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
        $tableName = DB::getTablePrefix().'jadwal_inspeksi';

        $existing = collect(DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = '{$tableName}'
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        "))->pluck('CONSTRAINT_NAME');

        Schema::table('jadwal_inspeksi', function (Blueprint $table) use ($existing) {
            if (! $existing->contains('jadwal_inspeksi_laporan_fk')) {
                $table->foreign(['id_laporan'], 'jadwal_inspeksi_laporan_fk')
                    ->references(['id_laporan'])->on('laporan_warga')
                    ->onUpdate('no action')->onDelete('cascade');
            }

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_inspeksi', function (Blueprint $table) {
            $table->dropForeign('jadwal_inspeksi_laporan_fk');
        });
    }
};
