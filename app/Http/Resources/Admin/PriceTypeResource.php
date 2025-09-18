<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class PriceTypeResource extends JsonResource
{
    public function toArray($request)
    {

        return [
            'id'         => $this->id,
            'code'       => $this->code,
            'name'       => $this->name,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
