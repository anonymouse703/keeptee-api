<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PropertyAmenity extends Pivot
{
    protected $table = 'property_tag';

    protected $fillable = [
        'property_id',
        'amenity_id',
    ];
}
