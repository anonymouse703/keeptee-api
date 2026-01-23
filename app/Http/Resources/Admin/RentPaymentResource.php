<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use App\Enums\RentPayment\Status;
use App\Enums\RentPayment\PaymentMethod;
use Illuminate\Http\Resources\Json\JsonResource;

class RentPaymentResource extends JsonResource
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
            'amount' => $this->amount,
            'due_date' => $this->due_date,
            'paid_at' => $this->paid_at,
            'status' => Status::class,
            'payment_method' => PaymentMethod::class,

            // RELATIONSHIPS
            'lease' => new LeaseResource($this->whenLoaded('lease')),
        ];
    }
}
