<?php

namespace App\Http\Requests\Admin\Banner;

use Illuminate\Foundation\Http\FormRequest;

class StoreBannerRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'title'      => 'required|string|max:191',
            'image_path' => 'required|string|max:191',
            'link'       => 'nullable|string|max:191|url',
            'type'       => 'required|in:main,catalog,promo',
            'order'      => 'required|integer|min:0',
            'is_active'  => 'required|boolean',
        ];
    }
}
