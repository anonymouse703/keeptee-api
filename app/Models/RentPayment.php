<?php

namespace App\Models;

use App\Enums\RentPayment\Status;
use App\Enums\RentPayment\PaymentMethod;
use App\Models\Traits\GeneratesPaymentReferenceId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentPayment extends Model
{
    use GeneratesPaymentReferenceId;

    protected $fillable = [
        'lease_id',
        'base_amount',
        'late_fee_amount',
        'total_amount',
        'paid_amount', 
        'due_date',
        'paid_at',
        'status',
        'payment_method',
        'reference_id',
        'notes'
    ];

    protected function casts(): array
    {
        return [
            'status' => Status::class,
            'payment_method' => PaymentMethod::class,
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'base_amount' => 'decimal:2',
            'late_fee_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }
}
