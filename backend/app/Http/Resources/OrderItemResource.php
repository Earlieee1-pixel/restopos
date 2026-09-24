<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

// Format sa usa ka item sulod sa order
class OrderItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'product'    => $this->whenLoaded('product', fn() => [
                'id'    => $this->product->id,
                'name'  => $this->product->name,
                'image' => $this->product->image,
            ]),
            'quantity'   => $this->quantity,
            'unit_price' => $this->unit_price,
            'subtotal'   => $this->subtotal,
            'notes'      => $this->notes,
        ];
    }
}
