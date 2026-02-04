<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PropertyAmenity extends Pivot
{
    protected $table = 'property_amenities';

    public $incrementing = false; 
    public $timestamps = false; 

    protected $fillable = [
        'property_id',
        'amenity_id',
    ];
}
