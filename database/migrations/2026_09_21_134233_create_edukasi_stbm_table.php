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
        Schema::create('edukasi_stbm', function (Blueprint $table) {
            $table->integer('id_edukasi', true);
            $table->integer('id_author')->index('id_author');
            $table->string('judul', 200);
            $table->enum('kategori', ['air_bersih', 'stbm', 'mhm']);
            $table->text('konten');
            $table->string('gambar_path')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('edukasi_stbm');
    }
};
