<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class ProductBulkRequest extends FormRequest
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
            'products' => 'required|array',
            "products.*.name" => "required|string|max:255",
            "products.*.code" => "required|string|max:255",
            "products.*.description" => "nullable|string",

            "products.*.producer_code" => "nullable|string|max:255",
            "products.*.category_code" => "required|string|max:255",

            'products.*.unit_id' => 'nullable|exists:units,id',
            "products.*.is_active" => "nullable|boolean",
            "products.*.status" => "nullable|string|max:255",
            'products.*.unit_coefficient' => 'nullable|numeric',
        ];
    }
}
