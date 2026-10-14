<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'name')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('name')->nullable();
            });
        } else {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('name')->nullable()->change();
            });
        }

        if (! Schema::hasColumn('users', 'email')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('email')->nullable();
            });
        } else {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('email')->nullable()->change();
            });
        }

        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'id_petugas')) {
                $table->string('id_petugas', 30)->nullable();
            }

            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role', 50)->nullable();
            }

            if (! Schema::hasColumn('users', 'wilayah_kerja')) {
                $table->string('wilayah_kerja', 150)->nullable();
            }

            if (! Schema::hasColumn('users', 'alamat')) {
                $table->text('alamat')->nullable();
            }

            if (! Schema::hasColumn('users', 'foto')) {
                $table->string('foto')->nullable();
            }

            if (! Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        $columnsToDrop = array_values(array_filter(
            ['id_petugas', 'role', 'wilayah_kerja', 'alamat', 'foto', 'deleted_at'],
            fn (string $column): bool => Schema::hasColumn('users', $column),
        ));

        if ($columnsToDrop !== []) {
            Schema::table('users', function (Blueprint $table) use ($columnsToDrop): void {
                $table->dropColumn($columnsToDrop);
            });
        }
    }
};
