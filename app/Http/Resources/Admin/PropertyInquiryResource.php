<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use App\Enums\PropertyInquiries\Status;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyInquiryResource extends JsonResource
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
            'visit_date' => $this->visit_date,
            'status' => Status::class,

            // RELATIONSHIPS
            'property' => new PropertyResource($this->whenLoaded('property')),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
