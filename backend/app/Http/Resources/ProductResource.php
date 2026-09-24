<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

// Format sa product data para sa API response
class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'description'  => $this->description,
            'price'        => $this->price,
            'image'        => $this->image,
            'is_available' => $this->is_available,
            // Kategorya sa produkto
            'category'     => $this->whenLoaded('category', fn() => [
                'id'         => $this->category->id,
                'name'       => $this->category->name,
                'icon'       => $this->category->icon,
                'sort_order' => $this->category->sort_order,
            ]),
            'category_id'  => $this->category_id,
        ];
    }
}
