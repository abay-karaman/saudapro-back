<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class ProductStockBulkRequest extends FormRequest
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
            'stocks' => 'required|array',
            "stocks.*.product_code" => "required|string|max:255",
            'stocks.*.stock' => 'required|string',
            'stocks.*.unit_id' => 'nullable|exists:units,id',
            'stocks.*.warehouse' => 'nullable|string|max:255',
        ];
    }
}
