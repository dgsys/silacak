<?php

namespace App\Policies;

use App\Models\Shipment;
use App\Models\User;

/**
 * Otorisasi per objek (mencegah IDOR): admin melihat semua paket,
 * user cabang hanya paket yang asal/tujuannya cabangnya sendiri.
 * Terdaftar otomatis (App\Policies\{Model}Policy).
 */
class ShipmentPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'cabang'], true);
    }

    public function view(User $user, Shipment $shipment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->role === 'cabang'
            && $user->branch_id !== null
            && in_array((int) $user->branch_id, [(int) $shipment->origin_branch_id, (int) $shipment->dest_branch_id], true);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || ($user->role === 'cabang' && $user->branch_id !== null);
    }

    public function update(User $user, Shipment $shipment): bool
    {
        return $this->view($user, $shipment);
    }
}
