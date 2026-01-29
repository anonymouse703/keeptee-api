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
            'sort_order' => $this->sort_order,
            'image_url' => $this->image_url,          
            'thumbnail_url' => $this->thumbnail_url,  
            'is_primary' => $this->is_primary,
        ];
    }
}
