<?php

namespace App\Models;

use App\Enums\Tenant\DocumentType;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TenantFile extends Pivot
{
    protected $table = 'tenant_file';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'file_id',
        'document_type',
    ];

    protected $casts = [
        'document_type' => DocumentType::class, 
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function file()
    {
        return $this->belongsTo(File::class);
    }
}
