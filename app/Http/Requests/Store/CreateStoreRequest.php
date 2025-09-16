<?php

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;

class CreateStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'latitude' => 'nullable|numeric|max:255',
            'longitude' => 'nullable|numeric|max:255',
            'phone' => 'nullable|string|max:255',
            'counterparty_id' => 'nullable|exists:counterparties,id',
        ];
    }
}
