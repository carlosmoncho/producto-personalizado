<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => $this->product?->name ?? 'Producto eliminado',
            'quantity' => $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'total_price' => (float) $this->total_price,

            // Configuración del producto
            'configuration' => [
                'selected_size' => $this->selected_size,
                'selected_color' => $this->selected_color,
                'selected_material' => $this->selected_material,
                'selected_print_colors' => $this->selected_print_colors ?? [],
                'selected_attributes' => $this->selected_attributes ?? [],
                'design_comments' => $this->design_comments,
            ],

            // Archivos de diseño
            'design_image' => $this->design_image,
            'design_image_url' => $this->design_image ? asset('storage/' . $this->design_image) : null,

            // Producto completo (solo cuando se carga)
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
