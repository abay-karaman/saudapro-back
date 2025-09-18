<?php

namespace App\Http\Requests\Admin\PriceType;

use Illuminate\Foundation\Http\FormRequest;

class StorePriceTypeRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'code' => 'required|string|max:191|unique:price_types,code',
            'name' => 'required|string|max:191',
        ];
    }
}
