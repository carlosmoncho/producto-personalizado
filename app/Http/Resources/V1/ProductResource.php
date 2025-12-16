<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Product API Resource
 *
 * Transforma el modelo Product en una respuesta JSON optimizada para el frontend
 */
class ProductResource extends JsonResource
{
    /**
     * Get the storage URL for a file path
     */
    protected function getStorageUrl(?string $path): ?string
    {
        return \App\Helpers\StorageHelper::url($path);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'description' => $this->description,

            // Imágenes
            'images' => collect($this->images ?? [])->map(fn($img) => $this->getStorageUrl($img))->toArray(),
            'main_image' => isset($this->images[0]) ? $this->getStorageUrl($this->images[0]) : null,

            // Modelo 3D
            'model_3d' => $this->when($this->model_3d_file, function() {
                $file = $this->model_3d_file;
                return [
                    'file' => $file,
                    'url' => $this->getStorageUrl($file),
                ];
            }),

            // Configurador
            'has_configurator' => (bool) $this->has_configurator,
            'configurator' => $this->when($this->has_configurator, [
                'base_price' => (float) $this->configurator_base_price,
                'description' => $this->configurator_description,
                'max_print_colors' => $this->max_print_colors,
                'allow_file_upload' => (bool) $this->allow_file_upload,
                'file_upload_types' => $this->file_upload_types,
            ]),

            // Pricing info
            'pricing_unit' => $this->pricing_unit ?? 'unit', // 'unit' o 'thousand'
            'pricing_unit_label' => $this->pricing_unit === 'thousand' ? 'por millar' : 'por unidad',

            // Opciones básicas (legacy - mantener por compatibilidad)
            'colors' => $this->colors ?? [],
            'materials' => $this->materials ?? [],
            'sizes' => $this->sizes ?? [],
            'print_colors' => $this->print_colors ?? [],
            'face_count' => $this->face_count,
            'print_colors_count' => $this->print_colors_count,

            // Atributos para filtros (simplificado - nombres por tipo)
            'filter_attributes' => $this->when($this->relationLoaded('productAttributes'), function() {
                return $this->productAttributes
                    ->filter(fn($attr) => $attr->active)
                    ->groupBy('type')
                    ->map(fn($attrs) => $attrs->pluck('name')->unique()->values()->toArray())
                    ->toArray();
            }),

            // Atributos configurables (nuevo sistema)
            'attributes' => $this->when($this->relationLoaded('productAttributes') && $this->has_configurator, function() {
                $attributesByType = $this->productAttributes->groupBy('type');

                // Obtener TODOS los atributos activos disponibles por tipo para el configurador
                $allColors = \App\Models\ProductAttribute::byType('color')->active()->orderBy('sort_order')->get();
                $allMaterials = \App\Models\ProductAttribute::byType('material')->active()->orderBy('sort_order')->get();
                $allSizes = \App\Models\ProductAttribute::byType('size')->active()->orderBy('sort_order')->get();
                $allInks = \App\Models\ProductAttribute::byType('ink')->active()->orderBy('sort_order')->get();

                // IDs de atributos asociados a este producto
                $productAttrIds = $this->productAttributes->pluck('id')->toArray();

                // Helper para obtener imágenes del pivot como URLs
                $getAttributeImages = function($attrId) {
                    $productAttr = $this->productAttributes->firstWhere('id', $attrId);
                    $images = $productAttr?->pivot->images ?? [];
                    if (empty($images)) return [];
                    return collect($images)->map(fn($img) => $this->getStorageUrl($img))->toArray();
                };

                return [
                    'colors' => $allColors->map(fn($attr) => [
                        'id' => $attr->id,
                        'name' => $attr->name,
                        'value' => $attr->value,
                        'hex_code' => $attr->hex_code,
                        'is_available' => in_array($attr->id, $productAttrIds),
                        'is_default' => $this->productAttributes->firstWhere('id', $attr->id)?->pivot->is_default ?? false,
                        'price_modifier' => $this->productAttributes->firstWhere('id', $attr->id)?->pivot->custom_price_modifier ?? 0,
                        'images' => $getAttributeImages($attr->id),
                    ])->values(),
                    'materials' => $allMaterials->map(fn($attr) => [
                        'id' => $attr->id,
                        'name' => $attr->name,
                        'value' => $attr->value,
                        'is_available' => in_array($attr->id, $productAttrIds),
                        'is_default' => $this->productAttributes->firstWhere('id', $attr->id)?->pivot->is_default ?? false,
                        'price_modifier' => $this->productAttributes->firstWhere('id', $attr->id)?->pivot->custom_price_modifier ?? 0,
                        'images' => $getAttributeImages($attr->id),
                    ])->values(),
                    'sizes' => $allSizes->map(fn($attr) => [
                        'id' => $attr->id,
                        'name' => $attr->name,
                        'value' => $attr->value,
                        'is_available' => in_array($attr->id, $productAttrIds),
                        'is_default' => $this->productAttributes->firstWhere('id', $attr->id)?->pivot->is_default ?? false,
                        'price_modifier' => $this->productAttributes->firstWhere('id', $attr->id)?->pivot->custom_price_modifier ?? 0,
                        'images' => $getAttributeImages($attr->id),
                    ])->values(),
                    'inks' => $allInks->map(fn($attr) => [
                        'id' => $attr->id,
                        'name' => $attr->name,
                        'value' => $attr->value,
                        'hex_code' => $attr->hex_code,
                        'is_available' => in_array($attr->id, $productAttrIds),
                        'is_default' => $this->productAttributes->firstWhere('id', $attr->id)?->pivot->is_default ?? false,
                        'price_modifier' => $this->productAttributes->firstWhere('id', $attr->id)?->pivot->custom_price_modifier ?? 0,
                        'images' => $getAttributeImages($attr->id),
                    ])->values(),
                ];
            }),

            // Relaciones
            'category' => new CategoryResource($this->whenLoaded('category')),
            'subcategory' => new SubcategoryResource($this->whenLoaded('subcategory')),
            'printing_systems' => PrintingSystemResource::collection($this->whenLoaded('printingSystems')),

            // Pricing
            'pricing_ranges' => $this->when($this->relationLoaded('pricing'), function() {
                return $this->pricing->map(function($price) {
                    return [
                        'quantity_from' => $price->quantity_from,
                        'quantity_to' => $price->quantity_to,
                        'unit_price' => (float) $price->unit_price,
                    ];
                });
            }),

            // Estado
            'active' => (bool) $this->active,

            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
