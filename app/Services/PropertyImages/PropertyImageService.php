<?php

namespace App\Services\PropertyImages;

use App\Models\Property;
use Illuminate\Support\Str;
use App\Models\PropertyImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\Uploader\ImageUploadService;
use Intervention\Image\Laravel\Facades\Image;

class PropertyImageService
{
    public function __construct(
        protected ImageUploadService $imageUploadService
    ) {}

    /**
     * Store property images with detailed metadata
     */
    public function store(Property $property, array $images, ?int $primaryIndex = null): void
    {
        $disk = config('filesystems.default');

        foreach ($images as $index => $image) {
            /** @var UploadedFile $image */

            // Upload original image
            $path = $this->imageUploadService->upload($image, 'properties/' . $property->id);

            // Generate thumbnail
            $thumbnailPath = $this->generateThumbnail($image, $property->id, $disk);

            // Get dimensions
            $dimensions = $this->getImageDimensions($image);

            $property->images()->create([
                'path' => $path,
                'thumbnail_path' => $thumbnailPath,
                'disk' => $disk,
                'name' => $image->getClientOriginalName(),
                'size' => $image->getSize(),
                'type' => $image->getMimeType(),
                'extension' => $image->getClientOriginalExtension(),
                'width' => $dimensions['width'] ?? null,
                'height' => $dimensions['height'] ?? null,
                'is_primary' => $primaryIndex !== null ? ($index === $primaryIndex) : ($index === 0),
                'sort_order' => $index + 1,
            ]);
        }
    }

    /**
     * Update existing images (reorder, change primary, etc.)
     */
    public function update(Property $property, array $data): void
    {
        if (isset($data['primary_image_id'])) {
            $property->images()->update(['is_primary' => false]);
            $property->images()->where('id', $data['primary_image_id'])->update(['is_primary' => true]);
        }

        if (isset($data['sort_order'])) {
            foreach ($data['sort_order'] as $imageId => $order) {
                $property->images()->where('id', $imageId)->update(['sort_order' => $order]);
            }
        }
    }

    /**
     * Delete a property image and its files
     */
    public function delete(PropertyImage $image): void
    {
        $this->imageUploadService->delete($image->path);

        if ($image->thumbnail_path) {
            $this->imageUploadService->delete($image->thumbnail_path);
        }

        $image->delete();
    }

    /**
     * Delete multiple images
     */
    public function deleteMultiple(array $imageIds): void
    {
        $images = PropertyImage::whereIn('id', $imageIds)->get();

        foreach ($images as $image) {
            $this->delete($image);
        }
    }

    /**
     * Generate thumbnail for a given image
     */
    protected function generateThumbnail(UploadedFile $image, int $propertyId, string $disk): ?string
    {
        try {
            $img = Image::make($image->getRealPath());

            // Resize to 300x300 while maintaining aspect ratio
            $img->resize(300, 300, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            $filename = Str::uuid() . '_thumb.' . $image->getClientOriginalExtension();
            $thumbnailPath = "properties/{$propertyId}/thumbnails/{$filename}";

            Storage::disk($disk)->put($thumbnailPath, $img->encode());

            return $thumbnailPath;
        } catch (\Exception $e) {
            Log::error('Thumbnail generation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get image dimensions
     */
    protected function getImageDimensions(UploadedFile $image): array
    {
        try {
            $imageInfo = getimagesize($image->getRealPath());
            return [
                'width' => $imageInfo[0] ?? null,
                'height' => $imageInfo[1] ?? null,
            ];
        } catch (\Exception) {
            return ['width' => null, 'height' => null];
        }
    }

    /**
     * Set a specific image as primary
     */
    public function setPrimary(PropertyImage $image): void
    {
        $image->property->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);
    }

    /**
     * Reorder images
     */
    public function reorder(Property $property, array $order): void
    {
        foreach ($order as $imageId => $sortOrder) {
            $property->images()->where('id', $imageId)->update(['sort_order' => $sortOrder]);
        }
    }
}
