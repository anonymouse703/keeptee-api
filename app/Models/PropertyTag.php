<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PropertyTag extends Pivot
{
    protected $table = 'property_tag';

    protected $fillable = [
        'property_id',
        'tag_id',
    ];
}
