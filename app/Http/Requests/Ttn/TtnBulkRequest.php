<?php

namespace App\Http\Requests\Ttn;

use Illuminate\Foundation\Http\FormRequest;

class TtnBulkRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255',
            'uid' => 'required|string|max:255',
            'date' => 'required|date',
            'status' => 'required|string|max:255',
            'courier_phone' => 'nullable|string|max:255',
            //'truck_code' => 'nullable|exists:trucks,code',
            'orders' => 'required|array|min:1',

            'orders.*.uid' => 'required|uuid',
            'orders.*.name' => 'required|string|max:255',
            //'orders.*.store_code' => 'required|string|max:255',
            'orders.*.counterparty_code' => 'nullable|exists:counterparties,code',
            'orders.*.reps_phone' => 'nullable|string|max:255',
            'orders.*.total_collected' => 'required|numeric|min:0',
            'orders.*.payment_method' => 'nullable|string|max:255',
            'orders.*.comment' => 'nullable|string|max:255',
            'orders.*.status' => 'required|string',

            'orders.*.items' => 'required|array|min:1',
            'orders.*.items.*.product_code' => 'required|string|exists:products,code',
            'orders.*.items.*.qty_collected' => 'required|string|regex:/^\d+(\.\d{1,2})?$/',
            'orders.*.items.*.price' => 'required|string|regex:/^\d+(\.\d{1,2})?$/',
            'orders.*.items.*.comment' => 'nullable|string|max:255',
        ];
    }
}
