<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
            "name" => "nullable|string|max:255",
            "code" => "nullable|string|max:255",
            "description" => "nullable|string",

            "producer_id" => "nullable|integer|exists:producers,id",
            "category_id" => "nullable|integer|exists:categories,id",
            'unit_id' => 'nullable|exists:units,id',
            "is_active" => "nullable|boolean",
            "status" => "nullable|string|max:255",
            'unit_coefficient' => 'nullable|numeric',
        ];
    }
}
