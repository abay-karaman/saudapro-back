<?php

namespace App\Http\Requests\Admin\Store;

use Illuminate\Foundation\Http\FormRequest;

class StoreStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'counterparty_id' => 'required|integer|exists:counterparties,id',
            'name'            => 'required|string|max:191',
            'code'            => 'nullable|string|max:191',
            'phone'           => 'nullable|string|max:191',
            'address'         => 'nullable|string',
            'latitude'        => 'nullable|string',
            'longitude'       => 'nullable|string',
        ];
    }
}
