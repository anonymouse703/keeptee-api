<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaseResource extends JsonResource
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
            'monthly_rent' => $this->monthly_rent,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,

            // RELATIONSHIPS
            'property' => new PropertyResource($this->whenLoaded('property')),
            'tenant' => new TenantResource($this->whenLoaded('tenant')),
        ];
    }
}
