<?php

namespace App\Models;

use App\Enums\Lease\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Lease extends Model
{
    protected $fillable = [
        'property_id',
        'tenant_id',
        'monthly_rent',
        'start_date',
        'end_date',
        'status',
        'terms',
        'notes',
        'file_id',

        'rent_due_day',
        'grace_period_days',
        'late_fee_type',
        'late_fee_value',
        'late_fee_cap',
    ];

    protected function casts(): array
    {
        return [
            'status' => Status::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'monthly_rent' => 'decimal:2',
            'late_fee_value' => 'decimal:4',
            'late_fee_cap' => 'decimal:2',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function rentPayments(): HasMany
    {
        return $this->hasMany(RentPayment::class);
    }

    
    public function files() : BelongsToMany
    {
        return $this->belongsToMany(File::class, 'lease_files')
                    ->using(LeaseFile::class) 
                    ->withPivot('document_type');
    }
}
