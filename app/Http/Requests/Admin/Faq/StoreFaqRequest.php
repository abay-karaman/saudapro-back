<?php

namespace App\Http\Requests\Admin\Faq;

use Illuminate\Foundation\Http\FormRequest;

class StoreFaqRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'question'   => 'required|string|max:191',
            'answer'     => 'required|string',
            'is_active'  => 'required|boolean',
        ];
    }
}
