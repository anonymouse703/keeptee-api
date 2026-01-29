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
     * Uses Intervention Image 3.x syntax
     */
    protected function generateThumbnail(UploadedFile $image, int $propertyId, string $disk): ?string
    {
        try {
            // Read the image using Intervention Image 3.x
            $img = Image::read($image->getRealPath());

            // Cover crops to exact 300x300 (good for property thumbnails)
            // Alternative: use ->scale(width: 300) to maintain aspect ratio
            $img->cover(300, 300);

            // Generate unique filename - force .jpg for consistency and smaller size
            $filename = Str::uuid() . '_thumb.jpg';
            $thumbnailPath = "properties/{$propertyId}/thumbnails/{$filename}";

            // Encode as JPEG with 85% quality for optimal file size/quality ratio
            $encodedImage = $img->toJpeg(85);

            // Save to storage
            Storage::disk($disk)->put($thumbnailPath, $encodedImage);

            return $thumbnailPath;
        } catch (\Exception $e) {
            Log::error('Thumbnail generation failed: ' . $e->getMessage(), [
                'property_id' => $propertyId,
                'original_image' => $image->getClientOriginalName(),
            ]);
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
        } catch (\Exception $e) {
            Log::warning('Failed to get image dimensions: ' . $e->getMessage());
            return ['width' => null, 'height' => null];
        }
    }

    /**
     * Set a specific image as primary
     */
    public function setPrimary(PropertyImage $image): void
    {
        // Reset all images for this property to non-primary
        $image->property->images()->update(['is_primary' => false]);
        
        // Set this image as primary
        $image->update(['is_primary' => true]);
    }

    /**
     * Reorder images
     */
    public function reorder(Property $property, array $order): void
    {
        foreach ($order as $imageId => $sortOrder) {
            $property->images()
                ->where('id', $imageId)
                ->update(['sort_order' => $sortOrder]);
        }
    }

    /**
     * Bulk update - change multiple images' primary status and sort order at once
     */
    public function bulkUpdate(Property $property, array $updates): void
    {
        foreach ($updates as $imageId => $data) {
            $updateData = [];
            
            if (isset($data['is_primary'])) {
                $updateData['is_primary'] = $data['is_primary'];
            }
            
            if (isset($data['sort_order'])) {
                $updateData['sort_order'] = $data['sort_order'];
            }
            
            if (!empty($updateData)) {
                $property->images()
                    ->where('id', $imageId)
                    ->update($updateData);
            }
        }
    }

    /**
     * Get primary image for a property
     */
    public function getPrimaryImage(Property $property): ?PropertyImage
    {
        return $property->images()
            ->where('is_primary', true)
            ->first();
    }

    /**
     * Get all images ordered by sort_order
     */
    public function getOrderedImages(Property $property): \Illuminate\Database\Eloquent\Collection
    {
        return $property->images()
            ->orderBy('sort_order')
            ->get();
    }
}