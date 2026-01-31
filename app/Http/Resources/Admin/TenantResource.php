<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $timezone = getModelTimezone($request->user());

        return [
            'id' => $this->id,
            'property_id' => $this->property_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'lease_start' => $this->lease_start?->format('M. d, Y') ?? null,
            'lease_end'   => $this->lease_end?->format('M. d, Y') ?? null,
            'created_at' => $this->created_at?->timezone($timezone)?->format('Y-m-d H:i:s') ?? null,

            // RELATIONSHIPS
            'property' => new PropertyResource($this->whenLoaded('property')),
        ];
    }
}
