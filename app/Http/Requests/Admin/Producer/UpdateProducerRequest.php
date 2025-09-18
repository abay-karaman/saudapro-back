<?php

namespace App\Http\Requests\Admin\Producer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProducerRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            "name" => "required|string|max:255",
            "code" => "nullable|string|max:255",
            "country" => "nullable|string|max:255",
        ];
    }
}
