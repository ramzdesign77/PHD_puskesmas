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
        if (Schema::hasTable('users')) {
            if (DB::table('users')->exists()) {
                throw new RuntimeException('Tabel users sudah berisi data dengan schema lama. Migrasi users dibatalkan agar data tidak terhapus.');
            }

            Schema::drop('users');
        }

        Schema::create('users', function (Blueprint $table) {
            $table->integer('id_user', true);
            $table->string('nama_lengkap', 150);
            $table->string('username', 50)->unique('username');
            $table->string('password');
            $table->string('no_telepon', 20)->nullable();
            $table->enum('role', ['sanitarian', 'staf_backup_kluster4', 'kepala_puskesmas', 'admin']);
            $table->boolean('is_active')->nullable()->default(true);
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
