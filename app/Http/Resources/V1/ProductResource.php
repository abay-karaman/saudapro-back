<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {

        $user = $request->user();
        $priceTypeId = $user->price_type_id ?? null;

        // Получаем цену для пользователя
        $price = $this->prices->firstWhere('price_type_id', $priceTypeId);
        // Проверка избранного
        $isFavorite = $user ? $this->favoritedBy->contains('id', $user->id) : false;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $request->routeIs('products.show') ? $this->description : null,
            'producer' => $this->producer?->name,
            'status' => $this->status,
            'is_favorite' => $isFavorite,

            'unit_id' => $this->unit_id,
            'unit_coefficient' => $this->unit_coefficient,
            'stock' => (string) ($this->stock?->stock < $this->unit_coefficient ? $this->unit_coefficient : $this->stock?->stock),
            // Только если авторизован
            'price' => $price ? $price->price : null,
            'discount' => ($price && $price->discount && $price->discount_expires_at && $price->discount_expires_at->isFuture())
                ? $price->discount
                : null,


            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ],
            //'images' => $this->images?->pluck('id','image_path')->toArray(),
            'images' => $this->images
                ? $this->images->map(fn($img) => Storage::disk('s3')->url($img->image_path))->toArray()
                : [],
        ];
    }
}
