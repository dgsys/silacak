<?php

namespace App\Http\Controllers;

use App\Http\Requests\OngkirRequest;
use App\Models\Service;
use App\Services\Ongkir\OngkirService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OngkirController extends Controller
{
    public function form(): View
    {
        return view('ongkir', ['services' => Service::aktif()->orderBy('tarif_per_kg')->get(['id', 'nama', 'tarif_per_kg', 'min_kg'])]);
    }

    /** POST + CSRF. Pola Post/Redirect/Get: hasil dikirim lewat flash session. */
    public function hitung(OngkirRequest $request, OngkirService $ongkir): RedirectResponse
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

        return redirect()->route('ongkir')->withInput()->with('hasil', $hasil + ['layanan' => $service->nama]);
    }
}
