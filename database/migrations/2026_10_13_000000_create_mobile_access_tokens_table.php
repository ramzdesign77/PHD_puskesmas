<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->integer('id_user');
            $table->string('name')->default('flutter-mobile');
            $table->char('token_hash', 64)->unique();
            $table->timestamp('created_at')->nullable();
            $table->foreign('id_user')->references('id_user')->on('users')->cascadeOnDelete();
            $table->index('id_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_access_tokens');
    }
};
