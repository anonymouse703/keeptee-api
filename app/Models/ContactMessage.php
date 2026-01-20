<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactMessage extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'message',
        'property_id',
    ];

    public function property() : BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
