<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class BulkStoreProductRequest extends FormRequest
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

            "products.*.producer" => "nullable",
            "products.*.producer.name" => "nullable|string|max:255",
            "products.*.producer.code" => "nullable|string|max:255",

            "products.*.category_code" => "required|string|max:255",
            "products.*.description" => "nullable|string",
            "products.*.is_active" => "nullable|boolean",
            "products.*.status" => "nullable|string|max:255",

            'products.*.unit_id' => 'nullable|exists:units,id',
            'products.*.unit_coefficient' => 'nullable|numeric',
            'products.*.stock' => 'required|string',

            'products.*.prices' => 'nullable|array',
            'products.*.prices.*.type' => 'required|string',
            'products.*.prices.*.type_code' => 'required|string',
            'products.*.prices.*.price' => 'required|string',
        ];
    }
}
