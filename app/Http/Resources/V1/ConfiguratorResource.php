<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class ConfiguratorResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'description' => $this->description,
            'configurator_description' => $this->configurator_description,

            // Images and 3D model
            'images' => $this->images,
            'model_3d_file' => $this->model_3d_file,

            // Configurator settings
            'has_configurator' => (bool) $this->has_configurator,
            'configurator_base_price' => (float) $this->configurator_base_price,
            'max_print_colors' => $this->max_print_colors,
            'allow_file_upload' => (bool) $this->allow_file_upload,
            'file_upload_types' => $this->file_upload_types,

            // Configuration rules
            'configurator_rules' => $this->configurator_rules,
            'configurator_settings' => $this->configurator_settings,

            // Physical properties
            'face_count' => $this->face_count,
            'print_colors_count' => $this->print_colors_count,

            // Status
            'active' => (bool) $this->active,

            // Category info
            'category_id' => $this->category_id,
            'subcategory_id' => $this->subcategory_id,

            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
