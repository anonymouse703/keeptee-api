<?php

namespace App\Services\PropertyImages;

use App\Models\Property;
use App\Models\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\FileUploader\Uploaders\PropertyImageUploader;
use Illuminate\Support\Facades\Auth;

class PropertyImageService
{
    /**
     * Store property images using pivot table approach
     */
    public function store(Property $property, array $images, ?int $primaryIndex = null, array $imageTypes = []): void
    {
        $fileData = [];

        foreach ($images as $index => $image) {
            try {
                // Upload image using PropertyImageUploader (creates record in files table)
                $fileId = PropertyImageUploader::uploadImage($image, Auth::user());

                // Prepare pivot data
                $fileData[$fileId] = [
                    'is_primary' => $primaryIndex !== null ? ($index === $primaryIndex) : ($index === 0),
                    'sort_order' => $index,
                    'image_type' => $imageTypes[$index] ?? null,
                ];
            } catch (\Exception $e) {
                Log::error('Failed to upload property image', [
                    'property_id' => $property->id,
                    'image_name' => $image->getClientOriginalName(),
                    'error' => $e->getMessage(),
                ]);
                // Continue with other images
            }
        }

        // Attach images to property via pivot table
        if (!empty($fileData)) {
            $property->images()->attach($fileData);
        }
    }

    /**
     * Update property images
     */
    public function update(
        Property $property,
        array $newImages = [],
        array $deleteFileIds = [],
        ?int $primaryImageId = null,
        array $imageTypes = []
    ): void {
        // Delete specified images
        if (!empty($deleteFileIds)) {
            $this->deleteImages($property, $deleteFileIds);
        }

        // Upload new images
        if (!empty($newImages)) {
            $existingCount = $property->images()->count();
            $fileData = [];

            foreach ($newImages as $index => $image) {
                try {
                    $fileId = PropertyImageUploader::uploadImage($image, Auth::user());

                    $fileData[$fileId] = [
                        'is_primary' => false, // Will set primary separately if needed
                        'sort_order' => $existingCount + $index,
                        'image_type' => $imageTypes[$index] ?? null,
                    ];
                } catch (\Exception $e) {
                    Log::error('Failed to upload property image', [
                        'property_id' => $property->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if (!empty($fileData)) {
                $property->images()->attach($fileData);
            }
        }

        // Update primary image
        if ($primaryImageId) {
            $this->setPrimaryImage($property, $primaryImageId);
        }
    }

    /**
     * Delete images from property
     */
    protected function deleteImages(Property $property, array $fileIds): void
    {
        // Detach from property
        $property->images()->detach($fileIds);

        // Delete files from storage if not used elsewhere
        foreach ($fileIds as $fileId) {
            $file = File::find($fileId);

            if ($file && !$file->properties()->exists() && !$file->tenants()->exists()) {
                // File is not used by any property or tenant, safe to delete
                Storage::disk($file->disk)->delete($file->path);

                if ($file->thumbnail_path) {
                    Storage::disk($file->disk)->delete($file->thumbnail_path);
                }

                $file->delete();
            }
        }
    }

    /**
     * Delete multiple images (alias for backward compatibility)
     */
    public function deleteMultiple(array $fileIds): void
    {
        // Find which properties these files belong to
        $files = File::whereIn('id', $fileIds)->get();
        
        foreach ($files as $file) {
            $properties = $file->properties;
            
            foreach ($properties as $property) {
                $this->deleteImages($property, [$file->id]);
            }
        }
    }

    /**
     * Set a specific image as primary
     */
    public function setPrimaryImage(Property $property, int $fileId): void
    {
        // Remove primary flag from all images
        $allFileIds = $property->images()->pluck('files.id')->toArray();
        
        if (!empty($allFileIds)) {
            $property->images()->updateExistingPivot($allFileIds, ['is_primary' => false]);
        }

        // Set new primary image
        $property->images()->updateExistingPivot($fileId, ['is_primary' => true]);
    }

    /**
     * Set a specific image as primary (using File model)
     */
    public function setPrimary(File $file): void
    {
        // Get the property this file belongs to
        $property = $file->properties()->first();
        
        if ($property) {
            $this->setPrimaryImage($property, $file->id);
        }
    }

    /**
     * Reorder images
     */
    public function reorder(Property $property, array $order): void
    {
        foreach ($order as $fileId => $sortOrder) {
            $property->images()->updateExistingPivot($fileId, ['sort_order' => $sortOrder]);
        }
    }

    /**
     * Bulk update - change multiple images' metadata at once
     */
    public function bulkUpdate(Property $property, array $updates): void
    {
        foreach ($updates as $fileId => $data) {
            $updateData = [];

            if (isset($data['is_primary'])) {
                $updateData['is_primary'] = $data['is_primary'];
            }

            if (isset($data['sort_order'])) {
                $updateData['sort_order'] = $data['sort_order'];
            }

            if (isset($data['image_type'])) {
                $updateData['image_type'] = $data['image_type'];
            }

            if (!empty($updateData)) {
                $property->images()->updateExistingPivot($fileId, $updateData);
            }
        }
    }

    /**
     * Get primary image for a property
     */
    public function getPrimaryImage(Property $property): ?File
    {
        return $property->images()
            ->wherePivot('is_primary', true)
            ->first();
    }

    /**
     * Get all images ordered by sort_order
     */
    public function getOrderedImages(Property $property): \Illuminate\Database\Eloquent\Collection
    {
        return $property->images()
            ->orderByPivot('sort_order')
            ->get();
    }

    /**
     * Update image type
     */
    public function updateImageType(Property $property, int $fileId, string $imageType): void
    {
        $property->images()->updateExistingPivot($fileId, ['image_type' => $imageType]);
    }

    /**
     * Get images by type
     */
    public function getImagesByType(Property $property, string $imageType): \Illuminate\Database\Eloquent\Collection
    {
        return $property->images()
            ->wherePivot('image_type', $imageType)
            ->orderByPivot('sort_order')
            ->get();
    }
}