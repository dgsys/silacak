<?php

namespace App\Http\Requests;

use App\Models\Shipment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Shipment::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'customer_nama' => ['required_without:customer_id', 'nullable', 'string', 'max:100'],
            'customer_telepon' => ['required_without:customer_id', 'nullable', 'regex:/^[0-9+\-\s]{8,20}$/'],
            'customer_alamat' => ['nullable', 'string', 'max:255'],
            'member' => ['sometimes', 'boolean'],

            'service_id' => ['required', 'integer', Rule::exists('services', 'id')->where('aktif', true)],
            'origin_branch_id' => [Rule::requiredIf(fn () => $this->user()?->isAdmin()), 'nullable', 'integer', 'exists:branches,id'],
            'dest_branch_id' => ['required', 'integer', 'exists:branches,id'],

            'penerima_nama' => ['required', 'string', 'max:100'],
            'penerima_alamat' => ['required', 'string', 'max:255'],

            'berat_aktual' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:100000'],
            'panjang' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'lebar' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'tinggi' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'nilai_barang' => ['nullable', 'integer', 'min:0', 'max:10000000000'],
        ];
    }
}
