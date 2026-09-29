<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourierAssignment extends Model
{
    protected $fillable = ['shipment_id', 'courier_id', 'jenis_tugas', 'status', 'ditugaskan_at', 'selesai_at'];

    protected function casts(): array
    {
        return ['ditugaskan_at' => 'datetime', 'selesai_at' => 'datetime'];
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(Courier::class);
    }
}
