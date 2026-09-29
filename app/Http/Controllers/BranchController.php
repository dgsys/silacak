<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Courier;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function index(): View
    {
        return view('branches.index', [
            'branches' => Branch::orderBy('nama')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('branches.form');
    }

    public function store(Request $request): RedirectResponse
    {
        Branch::create($this->validatedData($request));

        return redirect()->route('branches.index')->with('ok', 'Cabang berhasil ditambahkan.');
    }

    public function edit(Branch $branch): View
    {
        return view('branches.form', ['branch' => $branch]);
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $branch->update($this->validatedData($request, $branch));

        return redirect()->route('branches.index')->with('ok', 'Data cabang berhasil diperbarui.');
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        $hasShipments = Shipment::query()
            ->where('origin_branch_id', $branch->id)
            ->orWhere('dest_branch_id', $branch->id)
            ->exists();

        if ($hasShipments || Courier::where('branch_id', $branch->id)->exists() || User::where('branch_id', $branch->id)->exists()) {
            return back()->withErrors(['cabang' => 'Cabang tidak dapat dihapus karena masih digunakan paket, kurir, atau akun pengguna.']);
        }

        $branch->delete();

        return redirect()->route('branches.index')->with('ok', 'Cabang berhasil dihapus.');
    }

    private function validatedData(Request $request, ?Branch $branch = null): array
    {
        return $request->validate([
            'kode' => ['required', 'alpha_num:ascii', 'max:10', 'unique:branches,kode'.($branch ? ','.$branch->id : '')],
            'nama' => ['required', 'string', 'max:100'],
            'kota' => ['required', 'string', 'max:100'],
        ]);
    }
}