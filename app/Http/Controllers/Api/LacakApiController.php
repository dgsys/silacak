<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Tracking\TrackingService;
use Illuminate\Http\JsonResponse;

class LacakApiController extends Controller
{
    public function show(string $resi, TrackingService $tracking): JsonResponse
    {
        $hasil = $tracking->lookup($resi);

        if (! $hasil) {
            return response()->json(['message' => 'Resi tidak ditemukan.'], 404);
        }

        return response()->json(['data' => $hasil]);
    }
}
