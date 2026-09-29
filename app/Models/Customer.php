<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    // is_member sengaja TIDAK mass-assignable: hanya diubah eksplisit oleh admin.
    protected $fillable = ['nama', 'telepon', 'alamat'];

    protected function casts(): array
    {
        return ['is_member' => 'boolean'];
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }
}
