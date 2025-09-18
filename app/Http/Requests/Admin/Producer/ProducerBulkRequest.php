<?php

namespace App\Http\Requests\Producer;

use Illuminate\Foundation\Http\FormRequest;

class ProducerBulkRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'producers' => 'required|array',
            "producers.*.name" => "required|string|max:255",
            "producers.*.code" => "required|string|max:255",
            "producers.*.country" => "nullable|string|max:255",
        ];
    }
}
