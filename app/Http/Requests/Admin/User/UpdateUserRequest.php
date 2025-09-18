<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|unique:users,phone|string|max:15',
            'email' => 'nullable|string|email|max:255|unique:users,email',
            'password' => 'nullable|string|min:6',
            'role_id' => 'nullable|integer|exists:roles,id',
            'price_type_id' => 'nullable|integer|exists:price_types,id',
            'status' => 'nullable|string|max:255',
        ];
    }
}
