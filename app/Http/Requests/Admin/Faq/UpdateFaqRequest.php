<?php

namespace App\Http\Requests\Admin\Faq;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFaqRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'question'   => 'sometimes|required|string|max:191',
            'answer'     => 'sometimes|required|string',
            'is_active'  => 'sometimes|required|boolean',
        ];
    }
}
