<?php

namespace App\Services\PropertyImages;

use App\Models\Property;
use App\Models\File;
use App\Models\PropertyImage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\FileUploader\Uploaders\PropertyImageUploader;
use Illuminate\Support\Facades\Auth;

class PropertyImageService
{
    /**
     * Store property images using pivot table approach
     * 
     * @param Property $property
     * @param array $images Associative array with indices as keys
     * @param int|null $primaryIndex Index of the primary image
     * @param array $imageTypes Associative array with indices as keys
     */
    public function store(Property $property, array $images, ?int $primaryIndex = null, array $imageTypes = []): void
    {
        // Sort images and image types by their indices
        ksort($images);
        ksort($imageTypes);

        $pivotData = [];
        $sortOrder = 0;

        foreach ($images as $index => $image) {
            try {
                // 1. Upload image and create File record
                $fileId = PropertyImageUploader::uploadImage($image, Auth::user());

                Log::info('Image uploaded successfully', [
                    'property_id' => $property->id,
                    'file_id' => $fileId,
                    'original_index' => $index,
                    'sort_order' => $sortOrder
                ]);

                // 2. Prepare pivot data for property_images table
                $isPrimary = ($primaryIndex !== null) 
                    ? ($index == $primaryIndex) 
                    : ($sortOrder === 0); // First image is primary by default

                $pivotData[$fileId] = [
                    'is_primary' => $isPrimary,
                    'sort_order' => $sortOrder,
                    'image_type' => $imageTypes[$index] ?? 'exterior',
                ];

                $sortOrder++;

            } catch (\Exception $e) {
                Log::error('Failed to upload property image', [
                    'property_id' => $property->id,
                    'image_index' => $index,
                    'image_name' => $image->getClientOriginalName(),
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                // Continue with remaining images
            }
        }

        // 3. Attach files to property via property_images pivot table
        if (!empty($pivotData)) {
            $property->images()->attach($pivotData);
            
            Log::info('Property images attached successfully', [
                'property_id' => $property->id,
                'file_count' => count($pivotData),
                'file_ids' => array_keys($pivotData),
            ]);
        } else {
            Log::warning('No images were attached to property', [
                'property_id' => $property->id
            ]);
        }
    }

    /**
     * Update property images
     */
    public function update(
        Property $property,
        array $newImages = [],
        array $deleteFileIds = [],
        ?int $primaryFileId = null,
        array $imageTypes = []
    ): void {
        // Delete specified images first
        if (!empty($deleteFileIds)) {
            $this->deleteImages($property, $deleteFileIds);
        }

        // Upload new images
        if (!empty($newImages)) {
            $existingCount = $property->images()->count();
            $pivotData = [];
            $sortOrder = $existingCount;

            foreach ($newImages as $index => $image) {
                try {
                    $fileId = PropertyImageUploader::uploadImage($image, Auth::user());

                    $pivotData[$fileId] = [
                        'is_primary' => false,
                        'sort_order' => $sortOrder,
                        'image_type' => $imageTypes[$index] ?? 'exterior',
                    ];

                    $sortOrder++;
                } catch (\Exception $e) {
                    Log::error('Failed to upload property image during update', [
                        'property_id' => $property->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if (!empty($pivotData)) {
                $property->images()->attach($pivotData);
            }
        }

        // Update primary image if specified
        if ($primaryFileId) {
            $this->setPrimaryImage($property, $primaryFileId);
        }
    }

    /**
     * Delete images from property
     */
    protected function deleteImages(Property $property, array $fileIds): void
    {
        // Get PropertyImage records before detaching
        $propertyImages = PropertyImage::where('property_id', $property->id)
            ->whereIn('file_id', $fileIds)
            ->get();

        // Detach from pivot table
        $property->images()->detach($fileIds);

        // Delete physical files and File records if not used elsewhere
        foreach ($fileIds as $fileId) {
            $file = File::find($fileId);

            if ($file) {
                // Check if file is still used by other properties
                $stillInUse = PropertyImage::where('file_id', $fileId)
                    ->where('property_id', '!=', $property->id)
                    ->exists();

                if (!$stillInUse) {
                    // Delete physical file
                    if ($file->path && Storage::disk($file->disk)->exists($file->path)) {
                        Storage::disk($file->disk)->delete($file->path);
                    }

                    // Delete thumbnail
                    if ($file->thumbnail_path && Storage::disk($file->disk)->exists($file->thumbnail_path)) {
                        Storage::disk($file->disk)->delete($file->thumbnail_path);
                    }

                    // Delete File record
                    $file->delete();
                }
            }
        }
    }

    /**
     * Public API for deleting images for a specific property
     */
    public function deleteForProperty(Property $property, array $fileIds): void
    {
        $this->deleteImages($property, $fileIds);
    }

    /**
     * Delete multiple images (by PropertyImage IDs, not File IDs)
     */
    public function deleteMultiple(array $propertyImageIds): void
    {
        $propertyImages = PropertyImage::whereIn('id', $propertyImageIds)->get();
        
        foreach ($propertyImages as $propertyImage) {
            $property = $propertyImage->property;
            $this->deleteImages($property, [$propertyImage->file_id]);
        }
    }

    /**
     * Set a specific image as primary
     */
    public function setPrimaryImage(Property $property, int $fileId): void
    {
        // Get all file IDs for this property
        $allFileIds = $property->images()->pluck('files.id')->toArray();

        if (empty($allFileIds)) {
            return;
        }

        // Reset all images to non-primary
        foreach ($allFileIds as $id) {
            $property->images()->updateExistingPivot($id, ['is_primary' => false]);
        }

        // Set new primary image
        $property->images()->updateExistingPivot($fileId, ['is_primary' => true]);
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
     * Update image type
     */
    public function updateImageType(Property $property, int $fileId, string $imageType): void
    {
        $property->images()->updateExistingPivot($fileId, ['image_type' => $imageType]);
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
     * Get primary image
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
    public function getOrderedImages(Property $property)
    {
        return $property->images()
            ->orderByPivot('sort_order', 'asc')
            ->get();
    }

    /**
     * Get images by type
     */
    public function getImagesByType(Property $property, string $imageType)
    {
        return $property->images()
            ->wherePivot('image_type', $imageType)
            ->orderByPivot('sort_order', 'asc')
            ->get();
    }
}