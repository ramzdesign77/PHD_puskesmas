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
        Schema::table('inspeksi_ikl', function (Blueprint $table) {
            $table->foreign(['id_jadwal'], 'inspeksi_ikl_ibfk_1')->references(['id_jadwal'])->on('jadwal_inspeksi')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspeksi_ikl', function (Blueprint $table) {
            $table->dropForeign('inspeksi_ikl_ibfk_1');
        });
    }
};
