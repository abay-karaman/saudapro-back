<?php

namespace App\Http\Requests\Product;

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
            "name" => "string|max:255",
            "code" => "string|max:255",
            "producer" => "string|max:255",
            "preview_photo" => "nullable|string",
            "category_id" => "nullable|exists:categories,id",
            "description" => "nullable|string",
            "price" => "integer",
            "unit" => "string|max:255",
            "is_active" => "nullable|boolean",
        ];
    }
}
