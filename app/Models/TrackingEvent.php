<?php

namespace App\Models;

use App\Enums\ShipmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackingEvent extends Model
{
    public const UPDATED_AT = null; // tabel hanya punya created_at

    protected $fillable = ['shipment_id', 'branch_id', 'status', 'catatan', 'waktu'];

    protected function casts(): array
    {
        return ['waktu' => 'datetime', 'status' => ShipmentStatus::class];
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
