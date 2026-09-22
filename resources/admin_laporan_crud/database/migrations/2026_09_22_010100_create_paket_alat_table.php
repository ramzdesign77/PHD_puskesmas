<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paket_alat', function (Blueprint $table) {
            $table->id('id_paket');
            $table->string('nama_paket');
            // Samakan value ini dengan value option "Kategori Masalah" di form laporan warga
            $table->enum('kategori_laporan', [
                'Air Bersih',
                'Sanitasi',
                'Sampah',
                'Jentik Nyamuk',
                'Limbah',
                'PHBS',
                'Lainnya',
            ]);
            $table->text('deskripsi_alat')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paket_alat');
    }
};
