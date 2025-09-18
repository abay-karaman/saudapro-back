<?php

namespace App\Http\Requests\Admin\PriceType;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePriceTypeRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'code' => 'sometimes|required|string|max:191|unique:price_types,code',
            'name' => 'sometimes|required|string|max:191',
        ];
    }
}
