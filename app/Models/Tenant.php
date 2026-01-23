<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tenant extends Model
{
    protected $fillable = [
        'property_id',
        'name',
        'phone',
        'lease_start',
        'lease_end',
    ];

    public function property() : BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }
}
