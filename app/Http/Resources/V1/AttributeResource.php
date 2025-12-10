<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class AttributeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'attribute_group_id' => $this->attribute_group_id,
            'type' => $this->type,
            'name' => $this->name,
            'value' => $this->value,
            'slug' => $this->slug,
            'description' => $this->description,

            // Visual properties
            'hex_code' => $this->hex_code,
            'pantone_code' => $this->pantone_code,
            'ral_code' => $this->ral_code,
            'image_path' => $this->image_path ? url($this->image_path) : null,
            'thumbnail_path' => $this->thumbnail_path ? url($this->thumbnail_path) : null,

            // Pricing
            'price_modifier' => $this->when($this->pivotLoaded('product_attribute_values'), function() {
                return (float) ($this->pivot->custom_price_modifier ?? 0);
            }, 0),
            'price_percentage' => $this->when($this->pivotLoaded('product_attribute_values'), function() {
                return (float) ($this->pivot->custom_price_percentage ?? 0);
            }, 0),

            // Stock and weight
            'stock_quantity' => $this->stock_quantity,
            'weight_modifier' => (float) $this->weight_modifier,

            // Compatibility
            'compatible_materials' => $this->compatible_materials,
            'incompatible_with' => $this->incompatible_with,

            // Configuration
            'requires_file_upload' => (bool) $this->requires_file_upload,
            'metadata' => $this->metadata,

            // Status and ordering
            'sort_order' => $this->sort_order,
            'active' => (bool) $this->active,
            'is_recommended' => (bool) $this->is_recommended,

            // Additional info from pivot
            'is_default' => $this->when($this->pivotLoaded('product_attribute_values'), function() {
                return (bool) ($this->pivot->is_default ?? false);
            }, false),
            'is_available' => $this->when($this->pivotLoaded('product_attribute_values'), function() {
                return (bool) ($this->pivot->is_available ?? true);
            }, true),
            'min_quantity' => $this->when($this->pivotLoaded('product_attribute_values'), function() {
                return $this->pivot->min_quantity;
            }),
            'max_quantity' => $this->when($this->pivotLoaded('product_attribute_values'), function() {
                return $this->pivot->max_quantity;
            }),
            'additional_production_days' => $this->when($this->pivotLoaded('product_attribute_values'), function() {
                return $this->pivot->additional_production_days;
            }),
        ];
    }
}
