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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lengkap');
            $table->string('nik', 16);
            $table->string('nomor_telepon');
            $table->integer('village_id');
            $table->string('rt');
            $table->string('rw');
            $table->enum('kategori_laporan', ['Masalah air', 'Jamban/Sanitasi', 'Lainnya']);
            $table->text('deskripsi_masalah');
            $table->string('foto_bukti')->nullable();
            $table->enum('status', ['Pendataan/Pending', 'Ditinjau', 'Diproses', 'Selesai'])
                ->default('Pendataan/Pending');
            $table->timestamps();

            $table->foreign('village_id')
                ->references('id_desa')
                ->on('desa')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
