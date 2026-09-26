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
        Schema::create('log_notifikasi_wa', function (Blueprint $table) {
            $table->integer('id_log', true);
            $table->integer('id_laporan')->index('id_laporan');
            $table->string('no_wa_tujuan', 20);
            $table->text('pesan_terkirim');
            $table->enum('status_kirim', ['pending', 'terkirim', 'gagal'])->nullable()->default('pending');
            $table->timestamp('sent_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_notifikasi_wa');
    }
};
