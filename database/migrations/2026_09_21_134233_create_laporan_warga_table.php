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
        Schema::create('laporan_warga', function (Blueprint $table) {
            $table->integer('id_laporan', true);
            $table->string('kode_tiket', 20)->unique('kode_tiket');
            $table->string('nama_pelapor', 150);
            $table->char('nik_pelapor', 16);
            $table->string('no_wa', 20);
            $table->integer('id_desa')->index('id_desa');
            $table->string('rt', 5);
            $table->string('rw', 5);
            $table->enum('kategori_laporan', ['air_masalah', 'sanitasi_lingkungan'])->default('air_masalah');
            $table->text('deskripsi');
            $table->string('foto_bukti')->nullable();
            $table->enum('status_laporan', ['menunggu', 'dijadwalkan', 'selesai', 'ditolak'])->nullable()->default('menunggu');
            $table->text('alasan_penolakan')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_warga');
    }
};
