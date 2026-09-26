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
        Schema::table('log_notifikasi_wa', function (Blueprint $table) {
            $table->foreign(['id_laporan'], 'log_notifikasi_wa_ibfk_1')->references(['id_laporan'])->on('laporan_warga')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_notifikasi_wa', function (Blueprint $table) {
            $table->dropForeign('log_notifikasi_wa_ibfk_1');
        });
    }
};
