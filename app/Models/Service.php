<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['kode', 'nama', 'tarif_per_kg', 'min_kg', 'aktif'];

    protected function casts(): array
    {
        return ['aktif' => 'boolean', 'tarif_per_kg' => 'integer', 'min_kg' => 'integer'];
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('aktif', true);
    }
}
