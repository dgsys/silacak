<?php

namespace App\Http\Controllers;

use App\Services\Tracking\TrackingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LacakController extends Controller
{
    public function __invoke(Request $request, TrackingService $tracking): View
    {
        if (! $request->filled('resi')) {
            return view('lacak');
        }

        $data = $request->validate([
            'resi' => ['required', 'string', 'regex:/^[A-Za-z0-9]{8,20}$/'],
        ], [
            'resi.regex' => 'Format nomor resi tidak valid.',
        ]);

        return view('lacak', [
            'resi' => strtoupper($data['resi']),
            'hasil' => $tracking->lookup($data['resi']),
            'dicari' => true,
        ]);
    }
}
