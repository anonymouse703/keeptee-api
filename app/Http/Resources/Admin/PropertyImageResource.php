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
            'property_id' => $this->property_id,
            'file_id' => $this->file_id,
            'is_primary' => $this->is_primary,          
            'sort_order' => $this->sort_order,  
            'image_type' => $this->image_type,

            //relationship
            'file' => FileResource::collection($this->whenLoaded('file'))
        ];
    }
}
