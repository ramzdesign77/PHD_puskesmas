
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
        Schema::table('users', function (Blueprint $table) {
            $table->string('nama_lengkap', 30)->change();
            $table->string('id_petugas', 10)->nullable()->change();
            $table->string('username', 25)->change();
            $table->string('email', 30)->nullable()->change();
            $table->string('no_telepon', 15)->nullable()->change();
        });

        // Hapus kolom hanya jika kolom tersebut memang tersedia.
        $columnsToDrop = [];

        if (Schema::hasColumn('users', 'nip')) {
            $columnsToDrop[] = 'nip';
        }

        if (Schema::hasColumn('users', 'nik')) {
            $columnsToDrop[] = 'nik';
        }

        if (!empty($columnsToDrop)) {
            Schema::table('users', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nama_lengkap', 150)->change();
            $table->string('id_petugas', 30)->nullable()->change();
            $table->string('username', 50)->change();
            $table->string('email', 150)->nullable()->change();
            $table->string('no_telepon', 20)->nullable()->change();
        });

        // Tambahkan kembali kolom yang belum tersedia saja.
        if (!Schema::hasColumn('users', 'nip')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('nip', 30)->nullable()->after('nama_lengkap');
            });
        }

        if (!Schema::hasColumn('users', 'nik')) {
            Schema::table('users', function (Blueprint $table) {
                $table->char('nik', 16)->nullable()->after('nip');
            });
        }
    }
};
