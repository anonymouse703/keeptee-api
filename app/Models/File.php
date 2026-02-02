<?php

namespace App\Models;

use App\Enums\File\Disk;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;

class File extends Model
{
    protected $fillable = [
        'uploader_id',
        'path',
        'thumbnail_path',
        'disk',
        'name',
        'size',
        'type',
        'extension',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    public function properties()
    {
        return $this->belongsToMany(Property::class, 'property_images', 'file_id', 'property_id')
                    ->withPivot('is_primary', 'sort_order', 'image_type')
                    ->withTimestamps();
    }

    public function tenants()
    {
        return $this->belongsToMany(Tenant::class, 'tenant_files')
                    ->using(TenantFile::class)
                    ->withPivot('document_type');
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    public function fullUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!in_array($this->disk, Disk::cloudDisks(true))) {
                    return Storage::disk($this->disk)->url($this->path);
                }

                if (in_array($this->disk, Disk::s3Disks(true))) {
                    $cloudfrontDomain = config("filesystems.disks.$this->disk.cloudfront_domain");
                    if (! is_null($cloudfrontDomain)) {
                        return $cloudfrontDomain . '/' . $this->path;
                    }
                }

                return Storage::disk($this->disk)->url($this->path);
            },
        );
    }

    public function thumbnailUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (is_null($this->thumbnail_path)) {
                    return null;
                }

                if (!in_array($this->disk, Disk::cloudDisks(true))) {
                    return Storage::disk($this->disk)->url($this->thumbnail_path);
                }

                if (in_array($this->disk, Disk::s3Disks(true))) {
                    $cloudfrontDomain = config("filesystems.disks.$this->disk.cloudfront_domain");
                    if (! is_null($cloudfrontDomain)) {
                        return $cloudfrontDomain . '/' . $this->thumbnail_path;
                    }
                }

                return Storage::disk($this->disk)->url($this->thumbnail_path);
            },
        );
    }

    public function readableSize(): Attribute
    {
        return Attribute::make(
            get: function () {
                $bytes = $this->size;
                $units = ['B', 'KB', 'MB', 'GB'];
                
                for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
                    $bytes /= 1024;
                }
                
                return round($bytes, 2) . ' ' . $units[$i];
            },
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Check if file is an image.
     */
    public function isImage(): bool
    {
        return str_starts_with($this->type ?? '', 'image/');
    }

    /**
     * Check if file is a document.
     */
    public function isDocument(): bool
    {
        return in_array($this->type, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope: Files uploaded by a specific user.
     */
    public function scopeUploadedBy($query, $userId)
    {
        return $query->where('uploader_id', $userId);
    }

    /**
     * Scope: Files of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Images only.
     */
    public function scopeImages($query)
    {
        return $query->whereIn('type', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    /**
     * Scope: Documents only.
     */
    public function scopeDocuments($query)
    {
        return $query->whereIn('type', [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ]);
    }
}