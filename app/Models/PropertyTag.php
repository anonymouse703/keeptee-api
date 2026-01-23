<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyTag extends Model
{
    protected $fillable = [
        'property_id',
        'tag_id',
    ];
}
