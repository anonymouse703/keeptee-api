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
}
