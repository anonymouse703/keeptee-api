<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use App\Enums\Maintenance\Status;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceResource extends JsonResource
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
            'status' => Status::class,
            'schedule_date' => $this->schedule_date,

             // RELATIONSHIPS
            'property' => new PropertyResource($this->whenLoaded('property')),
            'tenant' => new TenantResource($this->whenLoaded('tenant')),
        ];
    }
}
