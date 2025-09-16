<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourierOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uid' => $this->uid,
            'counterparty_id' => $this->counterparty_id,
            'counterparty' => $this->counterparty?->name,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'total_price' => $this->total_price,
            'total_collected' => $this->total_collected,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'store_id' => $this->store_id,
            'store_address' => $this->store?->address,
            'lat' => $this->store?->latitude,
            'long' => $this->store?->longitude,
            'items_count' => $this->items_count,
            'items' => CourierOrderItemResource::collection(
                $this->whenLoaded('items')
            ),
            'comment' => $this->comment,
        ];
    }
}
