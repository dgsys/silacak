<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Validasi kalkulator ongkir publik (web & API). */
class OngkirRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_id' => ['required', 'integer', Rule::exists('services', 'id')->where('aktif', true)],
            'berat_aktual' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:100000'],
            'panjang' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'lebar' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'tinggi' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'nilai_barang' => ['nullable', 'integer', 'min:0', 'max:10000000000'],
            'member' => ['sometimes', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'service_id' => 'layanan',
            'berat_aktual' => 'berat aktual',
            'nilai_barang' => 'nilai barang',
        ];
    }
}
