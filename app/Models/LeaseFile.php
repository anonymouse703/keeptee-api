<?php

namespace App\Models;

use App\Enums\Lease\DocumentType;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaseFile extends Pivot
{
    protected $table = 'lease_files';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'lease_id',
        'file_id',
        'document_type',
    ];

    protected $casts = [
        'document_type' => DocumentType::class, 
    ];

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    public function file(): BelongsTo  
    {
        return $this->belongsTo(File::class);
    }
}