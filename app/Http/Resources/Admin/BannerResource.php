<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
{
    public function toArray($request)
    {

        return [
            'id'         => $this->id,
            'title'      => $this->title,
            'image_path' => $this->image_path,
            'link'       => $this->link,
            'type'       => $this->type,
            'order'      => $this->order,
            'is_active'  => (bool) $this->is_active,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
