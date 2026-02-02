<?php

namespace App\Http\Resources\Admin;

use App\Enums\Property\PropertyType;
use Illuminate\Http\Request;
use App\Enums\Property\Status;
use App\Models\PropertyImage;
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
            'created_at' => $this->created_at,

            // RELATIONSHIPS
            'owner' => new UserResource($this->whenLoaded('owner')),

            //PIVOT DATA
            'images' => $this->whenLoaded('propertyImages', function () {
                return $this->propertyImages->map(function ($propertyImage) {
                    return [
                        'file_id' => $propertyImage->file_id, 
                        'is_primary' => $propertyImage->is_primary,
                        'sort_order' => $propertyImage->sort_order,
                        'image_type' => $propertyImage->image_type?->value ?? $propertyImage->image_type,
                        'file' => $propertyImage->file ? [
                            'id' => $propertyImage->file->id,
                            'name' => $propertyImage->file->name,
                            'url' => $propertyImage->file->fullUrl,
                            'thumbnail' => $propertyImage->file->thumbnailUrl,
                            'size' => $propertyImage->file->readableSize,
                            'type' => $propertyImage->file->type,
                        ] : null,
                    ];
                });
            }),

        ];
    }
}
