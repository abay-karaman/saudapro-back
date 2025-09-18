<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {

        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'description' =>$this->description,
            'producer_id' => $this->producer_id,
            'category_code' => $this->category_code,
            'category_id' => $this->category_id,
            'unit_id' => $this->unit_id,
            'status' => $this->status,
            'unit_coefficient' => $this->unit_coefficient,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
