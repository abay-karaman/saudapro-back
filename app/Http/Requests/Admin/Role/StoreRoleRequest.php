<?php

namespace App\Http\Requests\Admin\Role;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'slug'        => 'nullable|string|max:191|unique:roles,slug',
            'name'        => 'required|string|max:191',
            'description' => 'nullable|array', // т.к. это JSON
        ];
    }
}
