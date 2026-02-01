<?php

namespace App\Models;

use App\Enums\Tenant\DocumentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tenant extends Model
{
    protected $fillable = [
        'property_id',
        'name',
        'email',
        'phone',
        'lease_start',
        'lease_end',
    ];

    protected $casts = [
        'lease_start' => 'date', 
        'lease_end' => 'date',
        'file_ids' => 'array',
        'document_type' => DocumentType::class,
    ];

    public function property() : BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function files() : BelongsToMany
    {
        return $this->belongsToMany(File::class, 'tenant_files')
                    ->using(TenantFile::class) 
                    ->withPivot('document_type');
    }

    // Access like this:
    // $tenantFile->pivot->document_type will be a string
}