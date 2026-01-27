<?php

namespace App\Services\Uploader;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUploadService
{
    /**
     * Upload file and return STORAGE PATH (not URL)
     */
    public function upload(
        UploadedFile $file,
        string $directory = 'properties'
    ): string {
        $disk = config('filesystems.default');

        $filename = (string) Str::uuid() . '.' . $file->getClientOriginalExtension();

        return $file->storeAs($directory, $filename, $disk);
    }

    /**
     * Delete file using storage path
     */
    public function delete(string $path): void
    {
        $disk = config('filesystems.default');

        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }
    }
}
