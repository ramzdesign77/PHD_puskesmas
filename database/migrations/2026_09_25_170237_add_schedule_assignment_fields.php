<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('jadwal_inspeksi', function (Blueprint $table) {
            $table->dropForeign('jadwal_inspeksi_ibfk_2');
            $table->dropIndex('id_operator');
            $table->unsignedBigInteger('id_operator')->nullable()->change();
            $table->time('waktu_kunjungan')->nullable()->after('tanggal_kunjungan');
            $table->text('catatan')->nullable()->after('status_kunjungan');
            $table->index('id_operator');
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
