<?php

namespace App\Models;

use App\Enums\Property\ImageType; 
use Illuminate\Database\Eloquent\Relations\Pivot;

class PropertyImage extends Pivot
{
    protected $table = 'property_images';

    public $incrementing = false;

    public $timestamps = false; 

    protected $fillable = [
        'property_id',
        'file_id',
        'is_primary',
        'sort_order',
        'image_type',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
        'image_type' => ImageType::class,
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}