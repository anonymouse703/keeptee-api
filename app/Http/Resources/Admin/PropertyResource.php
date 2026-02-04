<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'description'   => $this->description,
            'status'        => $this->status->value ?? $this->status,
            'property_type' => $this->property_type->value ?? $this->property_type,
            'price'         => $this->price,
            'bedrooms'      => $this->bedrooms,
            'bathrooms'     => $this->bathrooms,
            'floor_area'    => $this->floor_area,
            'address'       => $this->address,
            'city'          => $this->city,
            'state'         => $this->state,
            'country'       => $this->country,
            'latitude'      => $this->latitude,
            'longitude'     => $this->longitude,
            'owner_id'      => $this->owner_id,
            'is_featured'   => $this->is_featured,
            'is_active'     => $this->is_active,
            'created_at'    => $this->created_at,

            // RELATIONSHIPS
            'owner' => new UserResource($this->whenLoaded('owner')),

            // IMAGES (pivot-aware)
            'images' => $this->whenLoaded('images', function () {
                return $this->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'file_id' => $image->id, 
                        'path' => $image->path,
                        'url' => asset('storage/' . $image->path),
                        'thumbnail_url' => $image->thumbnail_path 
                            ? asset('storage/' . $image->thumbnail_path) 
                            : null,
                        'name' => $image->name,
                        'size' => $image->size,
                        'type' => $image->type,
                        
                        'is_primary' => $image->pivot->is_primary ?? false,
                        'sort_order' => $image->pivot->sort_order ?? 0,
                        'image_type' => $image->pivot->image_type ?? 'exterior',
                    ];
                });
            }),

            // AMENITIES
            'amenities' => $this->whenLoaded('amenities', function () {
                return $this->amenities->map(fn ($amenity) => [
                    'id'   => $amenity->id,
                    'name' => $amenity->name,
                ]);
            }),

            // TAGS
            'tags' => $this->whenLoaded('tags', function () {
                return $this->tags->map(fn ($tag) => [
                    'id'   => $tag->id,
                    'name' => $tag->name,
                ]);
            }),
        ];
    }
}
