<?php

namespace App\Models;

use App\Enums\RentPayment\Status;
use Illuminate\Database\Eloquent\Model;
use App\Enums\RentPayment\PaymentMethod;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentPayment extends Model
{
    protected $fillable = [
        'lease_id',
        'amount',
        'due_date',
        'paid_at',
        'status',
        'payment_method',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => Status::class,
            'payment_method' => PaymentMethod::class,
        ];
    }

    public function lease() : BelongsTo
    {
        return $this->belongsTo(Lease::class, 'lease_id');
    }
}
