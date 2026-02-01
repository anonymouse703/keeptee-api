<?php

namespace App\Services\FileUploader;

use App\Enums\File\Visibility;
use App\Models\File;
use App\Services\FileUploader\Jobs\GenerateThumbnailJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

class Uploader
{
    public string $disk;
    public string $startingPath;
    public string $storagePath = 'images';
    public string $uploadedPath;
    public string $fileName;
    public string $originalFilename;
    public bool $saveAsFilename = false;
    public int|bool $size;
    public string $mimeType;
    public string $extension;
    public File $file;
    protected Model $uploader;
    protected bool $hasExecuted = false;
    protected bool $generatesThumbnail = false;
    protected int $thumbnailWidth;
    protected int $thumbnailHeight;
    protected string $visibility = Visibility::Public->value;

    public function __construct(protected UploadedFile $uploadedFile)
    {
        $this->startingPath = app()->environment() . '/';
        $this->disk = config('filesystems.default');
        $this->fileName = $this->uploadedFile->getClientOriginalName();
        $this->originalFilename = $this->uploadedFile->getClientOriginalName();
        $this->size = $this->uploadedFile->getSize();  // bytes
        $this->mimeType = $this->uploadedFile->getMimeType();
        $this->extension = $this->uploadedFile->getClientOriginalExtension();
    }

    protected function execute(): File
    {
        $this->hasExecuted = true;

        $visibilityConfig = [
            'disk' => $this->disk,
            'visibility' => $this->visibility
        ];

        $filename = $this->saveAsFilename
            ? $this->fileName . '.' . $this->extension
            : null;

        $this->uploadedPath = $filename
            ? $this->uploadedFile->storeAs($this->getFilePath(), $filename, $visibilityConfig)
            : $this->uploadedFile->store($this->getFilePath(), $visibilityConfig);

        $this->save();

        if($this->generatesThumbnail) {
            GenerateThumbnailJob::dispatch($this->file, $this->thumbnailWidth, $this->thumbnailHeight);
        }

        return $this->file;
    }

    public function getFile(): File
    {
        return $this->execute();
    }

    public function uploader(Model $uploader): static
    {
        $this->uploader = $uploader;

        return $this;
    }

    public function fileName(string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function storagePath(string $storagePath): static
    {
        $this->storagePath = $storagePath;

        return $this;
    }

    public function disk(string $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    public function generateThumbnail(int $width = 300, int $height = 400): static
    {
        $this->generatesThumbnail = true;
        $this->thumbnailWidth = $width;
        $this->thumbnailHeight = $height;

        return $this;
    }

    protected function getFilePath(): string
    {

        return $this->startingPath . trim($this->storagePath, '/\\');
    }

    protected function save(): File
    {
        $this->file = File::create([
            'uploader_id' => $this->uploader->id,
            'path' => $this->uploadedPath,
            'disk' => $this->disk,
            'name' => $this->fileName,
            'original_filename' => $this->originalFilename,
            'size' => $this->size,
            'type' => $this->mimeType,
            'extension' => $this->extension,
            'visibility' => $this->visibility,
        ]);

        return $this->file;
    }

    public function __destruct()
    {
        if ($this->hasExecuted) {
            return;
        }

        $this->execute();
    }

    public function saveUsingFilename(bool $save = true): static
    {
        $this->saveAsFilename = $save;

        return $this;
    }

    public function setVisibility(Visibility $visibility = Visibility::Public): static
    {
        $this->visibility = $visibility->value;

        return $this;
    }
}
