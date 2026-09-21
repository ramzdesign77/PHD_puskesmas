<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'area')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('area');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'area')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('area', 100)->nullable()->after('role');
            });
        }
    }
};
