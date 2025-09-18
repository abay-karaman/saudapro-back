<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class ProducerResource extends JsonResource
{
    public function toArray($request)
    {

        return [
            'id'      => $this->id,
            'name'    => $this->name,
            'code'    => $this->code,
            'country' => $this->country,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
