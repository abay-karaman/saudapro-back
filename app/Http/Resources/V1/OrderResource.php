<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uid' => $this->uid,
            'representative_id' => $this->representative_id ?? null,
            'reps_phone' => $this->representative->phone ?? null,
            'counterparty_id' => $this->counterparty_id,
            'counterparty_code' => $this->counterparty?->code,
            'counterparty' => $this->counterparty?->name,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'total_price' => $this->total_price,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'store_id' => $this->store_id,
            'store_address' => $this->store?->address,
            'items_count' => $this->items_count,
            'items' => OrderItemResource::collection(
                $request->routeIs('orders.index', 'client.orders.index')
                    ? $this->whenLoaded('limitedItems')
                    : $this->whenLoaded('items')
            ),
            'comment' => $this->comment,
        ];
    }
}
