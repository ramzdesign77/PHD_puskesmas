<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'roles')) {
                $table->json('roles')->nullable();
            }

            if (! Schema::hasColumn('users', 'id_desa')) {
                $table->integer('id_desa')->nullable();
            }
        });

        DB::table('users')
            ->whereNull('roles')
            ->orderBy('id')
            ->chunkById(500, function ($users): void {
                foreach ($users as $user) {
                    $legacyRole = trim((string) $user->role);
                    $roles = $legacyRole === '' ? [] : explode(',', $legacyRole);
                    $roles = array_values(array_unique(array_filter(array_map(
                        static fn (string $role): string => trim($role),
                        $roles,
                    ))));

                    if ($roles !== []) {
                        DB::table('users')
                            ->where('id', $user->id)
                            ->whereNull('roles')
                            ->update(['roles' => json_encode($roles, JSON_THROW_ON_ERROR)]);
                    }
                }
            });
    }

    public function down(): void
    {
        // Keep the added role and village data even if this migration is rolled back.
    }
};
