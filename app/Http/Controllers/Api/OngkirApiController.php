<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OngkirRequest;
use App\Models\Service;
use App\Services\Ongkir\OngkirService;
use Illuminate\Http\JsonResponse;

class OngkirApiController extends Controller
{
    public function hitung(OngkirRequest $request, OngkirService $ongkir): JsonResponse
    {
        $d = $request->validated();
        $service = Service::aktif()->findOrFail($d['service_id']);

        $hasil = $ongkir->hitung(
            $service->tarif_per_kg,
            $service->min_kg,
            $d['berat_aktual'],
            (int) ($d['panjang'] ?? 0),
            (int) ($d['lebar'] ?? 0),
            (int) ($d['tinggi'] ?? 0),
            (int) ($d['nilai_barang'] ?? 0),
            (bool) ($d['member'] ?? false),
        );

        return response()->json(['data' => $hasil + ['layanan' => $service->nama]]);
    }
}
