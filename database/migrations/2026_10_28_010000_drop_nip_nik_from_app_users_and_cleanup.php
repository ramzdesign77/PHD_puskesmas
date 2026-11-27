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
        if (Schema::hasTable('app_users')) {
            $columnsToDrop = array_values(array_filter(
                ['nip', 'nik', 'nip_nik'],
                static fn (string $column): bool => Schema::hasColumn('app_users', $column),
            ));

            if ($columnsToDrop !== []) {
                Schema::table('app_users', function (Blueprint $table) use ($columnsToDrop): void {
                    $table->dropColumn($columnsToDrop);
                });
            }
        }

        if (Schema::hasTable('ref_edukasi')) {
            DB::table('ref_edukasi')->where('slug', 'langsungj')->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('app_users')) {
            return;
        }

        Schema::table('app_users', function (Blueprint $table): void {
            if (! Schema::hasColumn('app_users', 'nip')) {
                $table->string('nip', 30)->nullable();
            }

            if (! Schema::hasColumn('app_users', 'nik')) {
                $table->char('nik', 16)->nullable();
            }

            if (! Schema::hasColumn('app_users', 'nip_nik')) {
                $table->string('nip_nik', 30)->nullable();
            }
        });
    }
};
