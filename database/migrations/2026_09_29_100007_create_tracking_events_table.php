<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracking_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('shipment_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $t->foreignId('branch_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $t->string('status', 30);
            $t->string('catatan', 255)->nullable();
            $t->timestamp('waktu')->useCurrent();
            $t->timestamp('created_at')->nullable();

            // Indeks komposit: satu query untuk seluruh riwayat sebuah resi.
            $t->index(['shipment_id', 'waktu']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_events');
    }
};
