<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class ConfigurationResource extends JsonResource
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
            'product_id' => $this->product_id,
            'user_id' => $this->user_id,

            // Configuration data
            'attributes_base' => $this->attributes_base,
            'personalization' => $this->personalization,
            'files' => $this->files,
            'calculated' => $this->calculated,

            // Validation
            'status' => $this->status,
            'is_valid' => (bool) $this->is_valid,
            'validation_errors' => $this->validation_errors,

            // Metadata
            'expires_at' => $this->expires_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Relationships
            'product' => new ConfiguratorResource($this->whenLoaded('product')),
        ];
    }
}
