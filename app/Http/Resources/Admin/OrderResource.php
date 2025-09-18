<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'uid'               => $this->uid,
            'representative_id' => $this->representative_id,
            'counterparty_id'   => $this->counterparty_id,
            'store_id'          => $this->store_id,
            'total_price'       => $this->total_price,
            'total_collected'   => $this->total_collected,
            'total_delivered'   => $this->total_delivered,
            'status'            => $this->status,
            'comment'           => $this->comment,
            'payment_method'    => $this->payment_method,
            'source'            => $this->source,
            'created_at'        => $this->created_at?->toDateTimeString(),
            'updated_at'        => $this->updated_at?->toDateTimeString(),
            'deleted_at'        => $this->deleted_at?->toDateTimeString(),
        ];
    }
}
