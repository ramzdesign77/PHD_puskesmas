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
        Schema::table('edukasi_stbm', function (Blueprint $table) {
            $table->foreign(['id_author'], 'edukasi_stbm_ibfk_1')->references(['id_user'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('edukasi_stbm', function (Blueprint $table) {
            $table->dropForeign('edukasi_stbm_ibfk_1');
        });
    }
};
