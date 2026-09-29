<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('couriers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('branch_id')->constrained('branches')->restrictOnDelete()->cascadeOnUpdate();
            $t->string('nama', 100);
            $t->string('telepon', 20)->nullable();
            $t->boolean('aktif')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('couriers');
    }
};
