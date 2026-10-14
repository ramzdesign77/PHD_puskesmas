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
        Schema::create('inspeksi_ikl', function (Blueprint $table) {
            $table->integer('id_inspeksi', true);
            $table->integer('id_jadwal')->unique('id_jadwal');
            $table->enum('jenis_sarana_air', ['sumur_bor', 'sumur_terlindung', 'pdam', 'mata_air', 'lainnya']);
            $table->integer('jarak_sumber_pencemar');
            $table->boolean('p1_dinding_sumur_retak')->nullable()->default(false);
            $table->boolean('p2_penutup_tidak_rapat')->nullable()->default(false);
            $table->boolean('p3_lantai_becek_retak')->nullable()->default(false);
            $table->boolean('p4_spal_tersumbat')->nullable()->default(false);
            $table->boolean('p5_air_keruh_berbau')->nullable()->default(false);
            $table->integer('total_skor_ya')->nullable()->default(0);
            $table->enum('kategori_risiko', ['aman', 'risiko_sedang', 'risiko_tinggi']);
            $table->text('rekomendasi_sanitarian');
            $table->date('tanggal_inspeksi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspeksi_ikl');
    }
};
