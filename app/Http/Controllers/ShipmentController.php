<?php

namespace App\Http\Controllers;

use App\Enums\ShipmentStatus;
use App\Http\Requests\StoreShipmentRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Shipment;
use App\Services\Label\LabelService;
use App\Services\Ongkir\OngkirService;
use App\Services\Shipment\ShipmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ShipmentController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Shipment::class);

        $request->validate(['q' => ['nullable', 'regex:/^[A-Za-z0-9]{1,20}$/']]);
        $user = $request->user();
        $q = strtoupper((string) $request->query('q', ''));

        $shipments = Shipment::query()
            ->with(['service:id,nama', 'originBranch:id,kota', 'destBranch:id,kota'])
            // Cabang hanya melihat paket asal/tujuan cabangnya sendiri.
            ->when(! $user->isAdmin(), fn ($qb) => $qb->where(
                fn ($w) => $w->where('origin_branch_id', $user->branch_id)->orWhere('dest_branch_id', $user->branch_id)
            ))
            ->when($q !== '', fn ($qb) => $qb->where('resi', 'like', $q.'%')) // awalan: memakai indeks
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('shipments.index', ['shipments' => $shipments, 'q' => $q]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Shipment::class);
        $selectedCustomerId = $request->old('customer_id');
        $selectedCustomer = $selectedCustomerId ? Customer::find($selectedCustomerId) : null;

        return view('shipments.create', [
            'services' => Service::aktif()->orderBy('tarif_per_kg')->get(['id', 'nama', 'tarif_per_kg']),
            'branches' => Branch::orderBy('nama')->get(['id', 'nama', 'kota']),
            'selectedCustomer' => $selectedCustomer,
        ]);
    }

    public function store(StoreShipmentRequest $request, ShipmentService $service): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $originId = $user->isAdmin() ? (int) $data['origin_branch_id'] : (int) $user->branch_id;

        $customer = ! empty($data['customer_id'])
            ? Customer::findOrFail($data['customer_id'])
            : Customer::firstOrCreate(
                ['telepon' => $data['customer_telepon']],
                ['nama' => $data['customer_nama'], 'alamat' => $data['customer_alamat'] ?? null]
            );

        $shipment = $service->create($data, $customer, $originId);
        $shipment->loadMissing('service:id,nama');

        Log::channel('shipments')->info('resi.dibuat', [
            'nomor_resi' => $shipment->resi,
            'pelanggan_id' => $customer->id,
            'pelanggan_nama' => $customer->nama,
            'layanan' => $shipment->service->nama,
            'berat_aktual' => $shipment->berat_aktual,
            'berat_tagih' => $shipment->berat_tagih,
            'total_biaya' => $shipment->ongkir,
            'user_id' => $user->id,
            'user_email' => $user->email,
            'ip_address' => $request->ip(),
            'waktu' => now()->toIso8601String(),
        ]);

        return redirect()->route('shipments.show', $shipment)->with('ok', 'Paket dibuat. Nomor resi: '.$shipment->resi);
    }

    public function show(Shipment $shipment, OngkirService $ongkir): View
    {
        Gate::authorize('view', $shipment);

        $shipment->load([
            'service:id,nama,tarif_per_kg,min_kg', 'customer:id,nama,telepon,is_member',
            'originBranch:id,nama,kota', 'destBranch:id,nama,kota',
            'events' => fn ($q) => $q->orderByDesc('waktu')->orderByDesc('id'),
            'events.branch:id,nama,kota',
        ]);

        $biaya = $ongkir->hitung(
            $shipment->service->tarif_per_kg,
            $shipment->service->min_kg,
            $shipment->berat_aktual,
            $shipment->panjang,
            $shipment->lebar,
            $shipment->tinggi,
            $shipment->nilai_barang,
            $shipment->customer->is_member ?? false,
        );

        foreach (['biaya_dasar', 'diskon', 'asuransi'] as $komponen) {
            $biaya[$komponen] = $shipment->{$komponen} ?? $biaya[$komponen];
        }
        $biaya['total'] = $shipment->ongkir;

        return view('shipments.show', [
            'shipment' => $shipment,
            'biaya' => $biaya,
            'statuses' => ShipmentStatus::options(),
            'branches' => request()->user()->isAdmin() ? Branch::orderBy('nama')->get(['id', 'nama', 'kota']) : collect(),
        ]);
    }

    public function updateStatus(UpdateStatusRequest $request, Shipment $shipment, ShipmentService $service): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $status = ShipmentStatus::from($data['status']);
        $statusSebelumnya = $shipment->status_terakhir->value;

        $branchId = $user->isAdmin() ? (int) $data['branch_id'] : (int) $user->branch_id;

        $service->updateStatus($shipment, $status, $branchId, $data['catatan'] ?? null);

        Log::channel('shipments')->info('resi.status_diperbarui', [
            'nomor_resi' => $shipment->resi,
            'status_sebelumnya' => $statusSebelumnya,
            'status_baru' => $status->value,
            'branch_id' => $branchId,
            'user_id' => $user->id,
            'user_email' => $user->email,
            'ip_address' => $request->ip(),
            'waktu' => now()->toIso8601String(),
        ]);

        return back()->with('ok', 'Status diperbarui.');
    }

    public function label(Shipment $shipment, LabelService $labels): Response
    {
        Gate::authorize('view', $shipment);

        return $labels->render($shipment)->stream('label-'.$shipment->resi.'.pdf');
    }
}
