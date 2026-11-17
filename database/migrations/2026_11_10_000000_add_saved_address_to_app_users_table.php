<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('app_users')) {
            return;
        }

        Schema::table('app_users', function (Blueprint $table): void {
            if (! Schema::hasColumn('app_users', 'rt')) {
                $table->string('rt', 10)->nullable();
            }

            if (! Schema::hasColumn('app_users', 'rw')) {
                $table->string('rw', 10)->nullable();
            }

            if (! Schema::hasColumn('app_users', 'alamat_detail')) {
                $table->string('alamat_detail', 255)->nullable();
            }
        });
    }

    public function down(): void
    {
        // Keep saved address data when rolling back application code.
    }
};
