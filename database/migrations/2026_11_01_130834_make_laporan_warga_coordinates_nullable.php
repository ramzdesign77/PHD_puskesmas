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
        if (! Schema::hasTable('trx_laporan_warga')) {
            return;
        }

        Schema::table('trx_laporan_warga', function (Blueprint $table): void {
            if (Schema::hasColumn('trx_laporan_warga', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->change();
            }

            if (Schema::hasColumn('trx_laporan_warga', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('trx_laporan_warga')) {
            return;
        }

        foreach (['latitude', 'longitude'] as $column) {
            if (Schema::hasColumn('trx_laporan_warga', $column)
                && DB::table('trx_laporan_warga')->whereNull($column)->exists()) {
                throw new RuntimeException("Cannot make trx_laporan_warga.{$column} not nullable while NULL values exist.");
            }
        }

        Schema::table('trx_laporan_warga', function (Blueprint $table): void {
            if (Schema::hasColumn('trx_laporan_warga', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable(false)->change();
            }

            if (Schema::hasColumn('trx_laporan_warga', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable(false)->change();
            }
        });
    }
};
