<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PropertyImage extends Model
{
    protected $fillable = [
        'property_id',
        'path',
        'thumbnail_path',
        'disk',
        'name',
        'size',
        'type',
        'extension',
        'is_primary',
        'sort_order',
        'width',
        'height',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

     /**
     * Relationship: PropertyImage belongs to a Property
     */

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

     /**
     * Accessor: Get full URL for image_url attribute
     */
    public function getImageUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    /**
     * Accessor: Get full URL for thumbnail_url attribute
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail_path) {
            return null;
        }
        
        return Storage::disk($this->disk)->url($this->thumbnail_path);
    }

    /**
     * Get human-readable file size
     */
    public function getFormattedSizeAttribute(): string
    {
        if (!$this->size) {
            return 'Unknown';
        }
        
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->size;
        $unit = 0;
        
        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }
        
        return round($size, 2) . ' ' . $units[$unit];
    }
}
