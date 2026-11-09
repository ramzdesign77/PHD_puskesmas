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
        if (! Schema::hasTable('trx_inspeksi_ikl')) {
            return;
        }

        $columns = [
            'kategori_ikl' => static fn (Blueprint $table) => $table->string('kategori_ikl', 60)->nullable(),
            'p1' => static fn (Blueprint $table) => $table->boolean('p1')->default(false),
            'p2' => static fn (Blueprint $table) => $table->boolean('p2')->default(false),
            'p3' => static fn (Blueprint $table) => $table->boolean('p3')->default(false),
            'p4' => static fn (Blueprint $table) => $table->boolean('p4')->default(false),
            'p5' => static fn (Blueprint $table) => $table->boolean('p5')->default(false),
            'p6_indikator_6' => static fn (Blueprint $table) => $table->boolean('p6_indikator_6')->default(false),
            'p7_indikator_7' => static fn (Blueprint $table) => $table->boolean('p7_indikator_7')->default(false),
            'detail_indikator' => static fn (Blueprint $table) => $table->json('detail_indikator')->nullable(),
        ];

        foreach ($columns as $column => $definition) {
            if (! Schema::hasColumn('trx_inspeksi_ikl', $column)) {
                Schema::table('trx_inspeksi_ikl', $definition);
            }
        }

        if (Schema::hasColumn('trx_inspeksi_ikl', 'kategori_risiko')) {
            Schema::table('trx_inspeksi_ikl', function (Blueprint $table): void {
                $table->string('kategori_risiko', 50)->default('aman')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('trx_inspeksi_ikl')) {
            return;
        }

        $columns = array_values(array_filter(
            ['kategori_ikl', 'p1', 'p2', 'p3', 'p4', 'p5', 'p6_indikator_6', 'p7_indikator_7', 'detail_indikator'],
            static fn (string $column): bool => Schema::hasColumn('trx_inspeksi_ikl', $column),
        ));

        if ($columns !== []) {
            Schema::table('trx_inspeksi_ikl', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }
};
