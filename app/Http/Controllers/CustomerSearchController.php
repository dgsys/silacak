<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->merge(['q' => trim((string) $request->query('q', ''))]);
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:100'],
        ]);
        $keyword = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $validated['q']);

        $customers = Customer::query()
            ->where(fn ($query) => $query
                ->where('nama', 'like', $keyword.'%')
                ->orWhere('telepon', 'like', $keyword.'%'))
            ->orderBy('nama')
            ->limit(20)
            ->get(['id', 'nama', 'telepon', 'is_member']);

        return response()->json($customers);
    }
}