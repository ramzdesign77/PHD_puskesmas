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
        Schema::create('jadwal_inspeksi', function (Blueprint $table) {
            $table->integer('id_jadwal', true);
            $table->integer('id_laporan')->nullable()->index('id_laporan');
            $table->integer('id_operator')->index('id_operator');
            $table->date('tanggal_kunjungan');
            $table->enum('jenis_kunjungan', ['ikl_laporan_warga', 'ikl_rutin_rt'])->default('ikl_laporan_warga');
            $table->enum('status_kunjungan', ['terjadwal', 'selesai', 'batal'])->nullable()->default('terjadwal');
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_inspeksi');
    }
};
