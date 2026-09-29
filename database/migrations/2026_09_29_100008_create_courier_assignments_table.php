<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courier_assignments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('shipment_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $t->foreignId('courier_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $t->enum('jenis_tugas', ['JEMPUT', 'ANTAR']);
            $t->enum('status', ['DITUGASKAN', 'BERJALAN', 'SELESAI', 'GAGAL'])->default('DITUGASKAN');
            $t->timestamp('ditugaskan_at')->useCurrent();
            $t->timestamp('selesai_at')->nullable();
            $t->timestamps();

            $t->index(['courier_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courier_assignments');
    }
};
