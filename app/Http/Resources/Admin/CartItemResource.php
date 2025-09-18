<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $userPriceType = $request->user()?->price_type ?? 'A';
        $product = $this->whenLoaded('product');

        $price = $product?->priceFor($userPriceType);

        return [
            'product_id' => $this->product_id,
            'product_name' => $this->product?->name,
            'quantity' => $this->quantity,
            'price' => $price,
            'total' => $price ? $price * $this->quantity : 0,
        ];
    }
}
