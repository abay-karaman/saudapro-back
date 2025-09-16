<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $items = CartItemResource::collection($this->items)->resolve();
        $total = collect($items)->sum(fn($item)=> $item['total']);

        return [
            'store_id' => $this->store_id,
            'items' => $items,
            'total_price' => $total,
        ];
    }
}
