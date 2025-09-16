<?php

namespace App\Http\Resources\V1;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CounterpartyResource extends JsonResource
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
            'code' => $this->code,
            'uid' => $this->uid,
            'name' => $this->name,
            'bin_iin' => $this->bin_iin,
            'phone' => $this->phone,
            'stores' => StoreResource::collection($this->whenLoaded('stores')),
        ];
    }
}
