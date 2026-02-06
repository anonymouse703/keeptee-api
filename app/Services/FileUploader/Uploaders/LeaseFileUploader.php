<?php

namespace App\Services\FileUploader\Uploaders;

use App\Models\User;
use App\Services\FileUploader\Facades\FileUploader;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class LeaseFilesUploader
{
    protected ?User $user;
    protected UploadedFile $uploadedFile;
    protected string $storagePath = 'leases';
    protected bool $generateThumbnail = true;

    public function __construct(UploadedFile $uploadedFile, User $user)
    {
        $this->uploadedFile = $uploadedFile;
        $this->user = $user;
    }

    public static function uploadFile(UploadedFile $uploadedFile, User $user): int
    {
        $seriesUploader = new self($uploadedFile, $user);
        $seriesUploader->storagePath = 'lease_files';
        $seriesUploader->generateThumbnail = true;
        return $seriesUploader->upload();
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
