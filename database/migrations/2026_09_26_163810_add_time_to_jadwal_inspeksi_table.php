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
        Schema::table('jadwal_inspeksi', function (Blueprint $table): void {
            if (! Schema::hasColumn('jadwal_inspeksi', 'jam_mulai')) {
                $table->time('jam_mulai')->nullable();
            }

            if (! Schema::hasColumn('jadwal_inspeksi', 'jam_selesai')) {
                $table->time('jam_selesai')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columnsToDrop = array_values(array_filter(
            ['jam_mulai', 'jam_selesai'],
            fn (string $column): bool => Schema::hasColumn('jadwal_inspeksi', $column),
        ));

        if ($columnsToDrop !== []) {
            Schema::table('jadwal_inspeksi', function (Blueprint $table) use ($columnsToDrop): void {
                $table->dropColumn($columnsToDrop);
            });
        }
    }
};
