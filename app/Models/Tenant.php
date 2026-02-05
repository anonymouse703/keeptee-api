<?php

namespace App\Models;

use App\Enums\Tenant\DocumentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address'
    ];

    protected $casts = [
        'file_ids' => 'array',
        'document_type' => DocumentType::class,
    ];

    public function files() : BelongsToMany
    {
        return $this->belongsToMany(File::class, 'tenant_file')
                    ->using(TenantFile::class) 
                    ->withPivot('document_type');
    }
}