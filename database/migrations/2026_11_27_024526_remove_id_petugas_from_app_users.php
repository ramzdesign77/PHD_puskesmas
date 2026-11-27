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
        if (Schema::hasColumn('app_users', 'id_petugas')) {
            Schema::table('app_users', function (Blueprint $table): void {
                $table->dropColumn('id_petugas');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('app_users', 'id_petugas')) {
            Schema::table('app_users', function (Blueprint $table): void {
                $table->string('id_petugas', 30)->nullable();
            });
        }
    }
};
