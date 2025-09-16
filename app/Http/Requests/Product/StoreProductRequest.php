<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            "name" => "required|string|max:255",
            "code" => "required|string|max:255",
            "description" => "nullable|string",

            "producer" => "nullable",
            "producer.name" => "nullable|string|max:255",
            "producer.code" => "nullable|string|max:255",

            "category_code" => "required|string|max:255",
            'unit_id' => 'nullable|exists:units,id',
            "is_active" => "nullable|boolean",
            "status" => "nullable|string|max:255",
            'unit_coefficient' => 'nullable|numeric',
        ];
    }
}
