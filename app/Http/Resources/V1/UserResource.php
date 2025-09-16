<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'phone' => $this->phone,
            'status' => $this->status,
            'role' => $this->role->name ?? null,
            'orders_count' => $this->when(isset($this->orders_count), $this->orders_count),
            'cp_count' => $this->when(isset($this->counterparties_count), $this->counterparties_count),
        ];
    }
}
