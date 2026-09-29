<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete()->cascadeOnUpdate();
            $t->string('nama', 100);
            $t->string('email', 150)->unique();
            $t->string('password'); // hash bcrypt/argon2 (bukan MD5)
            $t->enum('role', ['admin', 'cabang', 'kurir'])->default('cabang');
            $t->rememberToken();
            $t->timestamps();
        });

        // Dipakai bila SESSION_DRIVER=database (default Laravel 11+).
        Schema::create('sessions', function (Blueprint $t) {
            $t->string('id')->primary();
            $t->foreignId('user_id')->nullable()->index();
            $t->string('ip_address', 45)->nullable();
            $t->text('user_agent')->nullable();
            $t->longText('payload');
            $t->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('users');
    }
};
