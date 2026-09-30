<?php

namespace App\Http\Controllers;

use App\Enums\ShipmentStatus;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Shipment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $shipments = Shipment::query();

        if (! $user->isAdmin()) {
            $shipments->where(function (Builder $query) use ($user): void {
                $query->where('origin_branch_id', $user->branch_id)
                    ->orWhere('dest_branch_id', $user->branch_id);
            });
        }

        $statusTotals = (clone $shipments)
            ->selectRaw('status_terakhir, COUNT(*) as aggregate')
            ->groupBy('status_terakhir')
            ->pluck('aggregate', 'status_terakhir');

        $statusCounts = collect(ShipmentStatus::cases())
            ->map(fn (ShipmentStatus $status): array => [
                'value' => $status->value,
                'label' => $status->label(),
                'count' => (int) $statusTotals->get($status->value, 0),
            ]);

        return view('dashboard', [
            'shipmentCount' => (clone $shipments)->count(),
            'customerCount' => $user->isAdmin()
                ? Customer::count()
                : (clone $shipments)->distinct()->count('customer_id'),
            'branchCount' => $user->isAdmin()
                ? Branch::count()
                : Branch::whereKey($user->branch_id)->count(),
            'statusCounts' => $statusCounts,
        ]);
    }
}