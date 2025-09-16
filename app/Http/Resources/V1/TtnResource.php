<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TtnResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        return [
            'id' => $this->id,
            'uid' => $this->uid,
            'name' => $this->name,
            'code' => $this->code,
            'date' => $this->date,
            'courier_id' => $this->courier_id,
            'status' => $this->status,
            'count' => $this->items->count(),
            'truck'      => $user?->trucks()->first()
                ? new TruckResource($user->trucks()->first())
                : null,
        ];
    }
}
