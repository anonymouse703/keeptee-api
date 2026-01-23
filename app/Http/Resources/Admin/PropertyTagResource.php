<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyTagResource extends JsonResource
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
            'name' => $this->name,
            'color' => $this->color,
            'description' => $this->description,
            'is_active' => $this->is_active,

            // RELATIONSHIPS
            // 'supplier' => new CategoryResource($this->whenLoaded('supplier')),
        ];
    }
}
