<?php

namespace App\Http\Requests\Admin\About;

use Illuminate\Foundation\Http\FormRequest;

class StoreAboutRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'call_center_phone' => 'required|string|max:191',
            'content'           => 'nullable|string',
            'is_active'         => 'required|boolean',
        ];
    }
}
