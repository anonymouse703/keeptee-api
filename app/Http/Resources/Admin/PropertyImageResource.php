<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'property_id' => $this->property_id,
            'image_url' => $this->image_url,
            'thumbnail_url' => $this->thumbnail_url,
            'name' => $this->name,
            'size' => $this->size,
            'type' => $this->type,
            'extension' => $this->extension,
            'width' => $this->width,
            'height' => $this->height,
            'is_primary' => (bool) $this->is_primary,
            'sort_order' => $this->sort_order,
        ];
    }
}
