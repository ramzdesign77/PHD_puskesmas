<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

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

        DB::statement('UPDATE users SET id_user = id WHERE id_user IS NULL OR id_user = 0');
        DB::statement("UPDATE users SET nama_lengkap = COALESCE(nama_lengkap, name) WHERE nama_lengkap IS NULL OR nama_lengkap = ''");
        DB::statement("UPDATE users SET role = COALESCE(role, 'sanitarian') WHERE role IS NULL OR role = ''");
        DB::statement('UPDATE users SET is_active = 1 WHERE is_active IS NULL');

        $usersWithoutUsername = DB::table('users')
            ->whereNull('username')
            ->orWhere('username', '')
            ->orderBy('id')
            ->get(['id', 'name']);

        foreach ($usersWithoutUsername as $user) {
            $baseUsername = Str::slug($user->name ?? '', '');
            $baseUsername = $baseUsername !== '' ? $baseUsername : 'user'.$user->id;
            $username = $baseUsername;
            $suffix = 1;

            while (DB::table('users')
                ->where('username', $username)
                ->where('id', '!=', $user->id)
                ->exists()) {
                $username = $baseUsername.$suffix;
                $suffix++;
            }

            DB::table('users')->where('id', $user->id)->update(['username' => $username]);
        }

        if (! Schema::hasIndex('users', ['id_user'], 'unique')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->unique('id_user', 'users_id_user_unique');
            });
        }

        if (! Schema::hasIndex('users', ['username'], 'unique')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->unique('username', 'users_username_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (Schema::getIndexes('users') as $index) {
            if (
                $index['unique']
                && in_array($index['columns'], [['id_user'], ['username']], true)
            ) {
                Schema::table('users', function (Blueprint $table) use ($index): void {
                    $table->dropUnique($index['name']);
                });
            }
        }

        $columnsToDrop = array_values(array_filter(
            ['id_user', 'nama_lengkap', 'username', 'no_telepon', 'is_active'],
            fn (string $column): bool => Schema::hasColumn('users', $column),
        ));

        if ($columnsToDrop !== []) {
            Schema::table('users', function (Blueprint $table) use ($columnsToDrop): void {
                $table->dropColumn($columnsToDrop);
            });
        }
    }
};
