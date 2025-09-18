<?php

namespace App\Http\Requests\Admin\About;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAboutRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'call_center_phone' => 'sometimes|required|string|max:191',
            'content'           => 'sometimes|required|string',
            'is_active'         => 'sometimes|required|boolean',
        ];
    }
}
