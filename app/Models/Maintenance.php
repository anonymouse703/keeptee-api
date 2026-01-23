<?php

namespace App\Models;

use App\Enums\Maintenance\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Maintenance extends Model
{
    protected $fillable = [
        'property_id',
        'tenant_id',
        'title',
        'description',
        'status',
        'scheduled_date',
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
