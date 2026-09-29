<?php

namespace App\Http\Requests;

use App\Enums\ShipmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        // {shipment} sudah di-resolve oleh route model binding (berdasarkan nomor resi).
        return $this->user()?->can('update', $this->route('shipment')) ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(ShipmentStatus::class)],
            'branch_id' => [Rule::requiredIf(fn () => $this->user()?->isAdmin()), 'nullable', 'integer', 'exists:branches,id'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ];
    }
}
