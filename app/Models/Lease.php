<?php

namespace App\Models;

use App\Enums\Lease\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lease extends Model
{
    protected $fillable = [
        'property_id',
        'tenant_id',
        'monthly_rent',
        'start_date',
        'end_date',
        'status',
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
        ];
    }

    public function property() : BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }
}
