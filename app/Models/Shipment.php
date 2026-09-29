<?php

namespace App\Models;

use App\Enums\ShipmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    protected $fillable = [
        'resi', 'customer_id', 'service_id', 'origin_branch_id', 'dest_branch_id',
        'penerima_nama', 'penerima_alamat', 'berat_aktual', 'panjang', 'lebar', 'tinggi',
        'berat_tagih', 'nilai_barang', 'ongkir', 'biaya_dasar', 'diskon', 'asuransi', 'status_terakhir',
    ];

    protected function casts(): array
    {
        return [
            'berat_aktual' => 'decimal:2',
            'status_terakhir' => ShipmentStatus::class,
        ];
    }

    /** URL memakai nomor resi (bukan id berurutan) agar tidak mudah ditebak. */
    public function getRouteKeyName(): string
    {
        return 'resi';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function originBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'origin_branch_id');
    }

    public function destBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'dest_branch_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(TrackingEvent::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(CourierAssignment::class);
    }
}
