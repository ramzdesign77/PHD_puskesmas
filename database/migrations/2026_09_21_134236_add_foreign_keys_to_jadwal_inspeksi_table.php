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
            $table->foreign(['id_laporan'], 'jadwal_inspeksi_ibfk_1')->references(['id_laporan'])->on('laporan_warga')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_operator'], 'jadwal_inspeksi_ibfk_2')->references(['id_user'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_inspeksi', function (Blueprint $table) {
            $table->dropForeign('jadwal_inspeksi_ibfk_1');
            $table->dropForeign('jadwal_inspeksi_ibfk_2');
        });
    }
};
