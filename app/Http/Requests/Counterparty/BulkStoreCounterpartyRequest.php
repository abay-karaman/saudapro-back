<?php

namespace App\Http\Requests\Counterparty;

use Illuminate\Foundation\Http\FormRequest;

class BulkStoreCounterpartyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'counterparties' => 'required|array|min:1',
            'counterparties.*.name' => 'required|string|max:255',
            'counterparties.*.code' => 'nullable|string|max:255',
            'counterparties.*.UID' => 'nullable|string|max:255',
            'counterparties.*.bin_iin' => 'nullable|string|max:255',
            'counterparties.*.phone' => 'nullable|string|max:255',
            'counterparties.*.rep_phones' => 'nullable|array',
            'counterparties.*.rep_phones.*' => 'nullable|string|max:255',
        ];
    }
}
