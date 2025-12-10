<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubcategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image' => $this->image,
            'active' => (bool) $this->active,
            'sort_order' => $this->sort_order,
            'category_id' => $this->category_id,

            // Categoría padre (solo cuando se carga)
            'category' => new CategoryResource($this->whenLoaded('category')),

            // Conteo de productos (solo cuando se carga)
            'products_count' => $this->when(isset($this->products_count), $this->products_count),

            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
