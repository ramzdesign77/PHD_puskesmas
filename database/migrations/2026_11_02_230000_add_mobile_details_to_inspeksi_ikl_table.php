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
        Schema::table('trx_inspeksi_ikl', function (Blueprint $table) {
            if (! Schema::hasColumn('trx_inspeksi_ikl', 'catatan_petugas')) {
                $table->text('catatan_petugas')->nullable();
            }

            if (! Schema::hasColumn('trx_inspeksi_ikl', 'foto_bukti')) {
                $table->string('foto_bukti')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trx_inspeksi_ikl', function (Blueprint $table) {
            $columns = array_values(array_filter(
                ['catatan_petugas', 'foto_bukti'],
                static fn (string $column): bool => Schema::hasColumn('trx_inspeksi_ikl', $column),
            ));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
