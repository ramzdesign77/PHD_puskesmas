<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'id_petugas')) {
                $table->string('id_petugas', 30)->nullable();
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

            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role', 50)->nullable();
            }

            if (! Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = ['id_petugas', 'wilayah_kerja', 'alamat', 'foto', 'deleted_at'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
