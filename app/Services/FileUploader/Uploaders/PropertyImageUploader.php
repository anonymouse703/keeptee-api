<?php

namespace App\Services\FileUploader\Uploaders;

use App\Models\User;
use App\Services\FileUploader\Facades\FileUploader;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class PropertyImageUploader
{
    protected ?User $user;
    protected UploadedFile $uploadedFile;
    protected string $storagePath = 'property_images';
    protected bool $generateThumbnail = true;

    public function __construct(UploadedFile $uploadedFile, User $user)
    {
        $this->uploadedFile = $uploadedFile;
        $this->user = $user;
    }

    /**
     * Upload a property image and return the file ID.
     */
    public static function uploadImage(UploadedFile $uploadedFile, User $user): int
    {
        $uploader = new self($uploadedFile, $user);
        $uploader->storagePath = 'property_images';
        $uploader->generateThumbnail = true;
        return $uploader->upload();
    }

    protected function upload(): int
    {
        $file = FileUploader::upload($this->uploadedFile)
            ->uploader($this->user)
            ->storagePath($this->storagePath)
            ->fileName(Str::random(8));

        if ($this->generateThumbnail) {
            $file->generateThumbnail(300, 300);
        }

        return $file->getFile()->id;
    }
}