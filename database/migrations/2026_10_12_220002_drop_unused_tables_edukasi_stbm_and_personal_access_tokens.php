<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (['edukasi_stbm', 'personal_access_tokens'] as $tableName) {
            if (Schema::hasTable($tableName) && DB::table($tableName)->exists()) {
                throw new RuntimeException("Cannot drop {$tableName} because it contains data.");
            }
        }

        Schema::dropIfExists('edukasi_stbm');
        Schema::dropIfExists('personal_access_tokens');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('edukasi_stbm')) {
            Schema::create('edukasi_stbm', function (Blueprint $table) {
                $table->increments('id_edukasi');
                $table->unsignedBigInteger('id_author')->index('id_author');
                $table->string('judul', 200);
                $table->enum('kategori', ['air_bersih', 'stbm', 'mhm']);
                $table->text('konten');
                $table->string('gambar_path')->nullable();
                $table->timestamp('created_at')->nullable()->useCurrent();

                $table->foreign('id_author', 'edukasi_stbm_ibfk_1')
                    ->references('id_user')
                    ->on('users')
                    ->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('personal_access_tokens')) {
            Schema::create('personal_access_tokens', function (Blueprint $table) {
                $table->id();
                $table->morphs('tokenable');
                $table->text('name');
                $table->string('token', 64)->unique();
                $table->text('abilities')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('expires_at')->nullable()->index();
                $table->timestamps();
            });
        }
    }
};
