<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

// Format sa order data para sa API response
class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'order_number'    => $this->order_number,
            'status'          => $this->status,
            'order_type'      => $this->order_type,
            'total_amount'    => $this->total_amount,
            'discount'        => $this->discount,
            'amount_tendered' => $this->amount_tendered,
            'change'          => $this->change,
            'notes'           => $this->notes,
            // Kinsa ang nag-take sa order
            'cashier'         => $this->whenLoaded('cashier', fn() => [
                'id'   => $this->cashier->id,
                'name' => $this->cashier->name,
            ]),
            // Unsang mesa
            'table'           => $this->whenLoaded('table', fn() => [
                'id'           => $this->table?->id,
                'table_number' => $this->table?->table_number,
            ]),
            // Mga gi-order nga pagkaon
            'items'           => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at'      => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
