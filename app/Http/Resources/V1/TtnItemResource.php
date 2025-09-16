<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TtnItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'ttn_id' => $this->ttn_id,
            'order_id' => $this->order_id,
            //'status' => $this->status,
            'counterparty_name' => $this->order->counterparty->name,
            'total_collected' => $this->order->total_collected,
            'order_status' => $this->order->status,
            'count' => $this->order->items->count(),
        ];
    }
}
