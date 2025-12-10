<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,

            // Cliente
            'customer' => [
                'name' => $this->customer_name,
                'email' => $this->customer_email,
                'phone' => $this->customer_phone,
                'address' => $this->customer_address,
            ],

            // Estado
            'status' => $this->status,
            'status_label' => $this->status_label,

            // Precios
            'total_amount' => (float) $this->total_amount,

            // Items
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'items_count' => $this->when($this->relationLoaded('items'), $this->items->count()),

            // Notas
            'notes' => $this->notes,

            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'approved_at' => $this->approved_at?->toISOString(),
            'shipped_at' => $this->shipped_at?->toISOString(),
            'delivered_at' => $this->delivered_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // URLs - Comentado temporalmente
            // 'urls' => [
            //     'view' => route('api.v1.orders.show', $this->id),
            // ],
        ];
    }
}
