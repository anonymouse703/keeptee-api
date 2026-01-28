<?php

namespace App\Http\Resources\Admin;

use App\Enums\Property\PropertyType;
use Illuminate\Http\Request;
use App\Enums\Property\Status;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            // 'status' => Status::class,
            // 'property_type' => PropertyType::class,
            'status' => $this->status->value ?? $this->status, 
            'property_type' => $this->property_type->value ?? $this->property_type,
            'price' => $this->price,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'floor_area' => $this->floor_area,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'owner_id' => $this->owner_id,
            'is_featured' => $this->is_featured,
            'is_active' => $this->is_active,

            // RELATIONSHIPS
            'images' => PropertyImageResource::collection($this->whenLoaded('images')),
            'owner' => new UserResource($this->whenLoaded('owner')),
        ];
    }
}
