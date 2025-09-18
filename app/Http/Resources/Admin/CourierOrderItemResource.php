<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CourierOrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'product_id' => $this->product_id,
            'product_name' => $this->product?->name,
            'product_image' => $this->product?->images?->first()?->image_path
                ? Storage::disk('s3')->url($this->product->images->first()->image_path)
                : null,
            'unit_coefficient' => $this->product?->unit_coefficient,
            'quantity' => $this->quantity,
            'qty_collected' => $this->qty_collected,
            'price_per_item' => $this->price,
            'total' => $this->price * $this->qty_collected,
            'comment' => $this->comment,
        ];
    }
}
