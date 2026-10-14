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
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'name')) {
                $table->string('name')->nullable()->change();
            }

            if (! Schema::hasColumn('users', 'id_user')) {
                $table->unsignedBigInteger('id_user')->nullable()->after('id');
            }

            if (! Schema::hasColumn('users', 'nama_lengkap')) {
                $table->string('nama_lengkap', 150)->nullable()->after('id_user');
            }

            if (! Schema::hasColumn('users', 'id_petugas')) {
                $table->string('id_petugas', 30)->nullable()->after('nama_lengkap');
            }

            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username', 50)->nullable()->after('id_petugas');
            }

            if (! Schema::hasColumn('users', 'no_telepon')) {
                $table->string('no_telepon', 20)->nullable()->after('password');
            }

            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role', 50)->nullable()->after('no_telepon');
            }

            if (! Schema::hasColumn('users', 'wilayah_kerja')) {
                $table->string('wilayah_kerja', 150)->nullable()->after('role');
            }

            if (! Schema::hasColumn('users', 'alamat')) {
                $table->text('alamat')->nullable()->after('wilayah_kerja');
            }

            if (! Schema::hasColumn('users', 'foto')) {
                $table->string('foto')->nullable()->after('alamat');
            }

            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('foto');
            }

            if (! Schema::hasColumn('users', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable()->after('updated_at');
            }
        });

        $usersTable = DB::getTablePrefix().'users';
        $userIdIndexExists = DB::getDriverName() === 'sqlite'
            ? DB::selectOne("SELECT name FROM sqlite_master WHERE type = 'index' AND name = 'users_id_user_unique'")
            : DB::selectOne("SHOW INDEX FROM `{$usersTable}` WHERE Key_name = 'users_id_user_unique'");

        if ($userIdIndexExists === null) {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('id_user', 'users_id_user_unique');
            });
        }

        DB::statement('UPDATE users SET id_user = id WHERE id_user IS NULL OR id_user = 0');
        DB::statement("UPDATE users SET nama_lengkap = COALESCE(nama_lengkap, name) WHERE nama_lengkap IS NULL OR nama_lengkap = ''");
        DB::statement("UPDATE users SET username = COALESCE(username, REPLACE(name, ' ', '')) WHERE username IS NULL OR username = ''");
        DB::statement("UPDATE users SET role = COALESCE(role, 'admin') WHERE role IS NULL OR role = ''");
        DB::statement('UPDATE users SET is_active = 1 WHERE is_active IS NULL');

        if (DB::getDriverName() !== 'sqlite') {
            $tableName = DB::getTablePrefix().'users';
            $indexExists = DB::selectOne("SHOW INDEX FROM `{$tableName}` WHERE Key_name = 'username'");

            if ($indexExists === null) {
                DB::statement('ALTER TABLE users ADD UNIQUE KEY users_username_unique (username)');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['id_user', 'nama_lengkap', 'id_petugas', 'username', 'no_telepon', 'role', 'wilayah_kerja', 'alamat', 'foto', 'is_active', 'deleted_at']);
        });
    }
};
