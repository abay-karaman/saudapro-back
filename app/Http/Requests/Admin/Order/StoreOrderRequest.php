<?php

namespace App\Http\Requests\Admin\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
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
            'uid'               => 'nullable|string|size:36',
            'representative_id' => 'nullable|integer|exists:users,id',
            'counterparty_id'   => 'nullable|integer|exists:counterparties,id',
            'store_id'          => 'nullable|integer|exists:stores,id',
            'total_price'       => 'nullable|numeric|min:0',
            'total_collected'   => 'nullable|numeric|min:0',
            'total_delivered'   => 'nullable|numeric|min:0',
            'status'            => 'nullable|in:new,in_progress,collected,loaded,on_way,delivered,cancelled',
            'comment'           => 'nullable|string',
            'payment_method'    => 'nullable|in:cash,card,debt',
            'source'            => 'nullable|in:app,1C',
        ];
    }
}
