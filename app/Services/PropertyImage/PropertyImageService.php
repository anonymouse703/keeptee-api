<?php

namespace App\Services\PropertyImage;

use App\Models\File;
use App\Models\Property;
use App\Models\PropertyImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\FileUploader\Uploaders\PropertyImageUploader;

class PropertyImageService
{   
    /**
     * @param Property $property
     * @param array $images Associative array with indices as keys
     * @param int|null $primaryIndex Index of the primary image
     * @param array $imageTypes Associative array with indices as keys
     */
    public function store(Property $property, array $images, ?int $primaryIndex = null, array $imageTypes = []): void
    {
        ksort($images);
        ksort($imageTypes);

        $pivotData = [];
        $sortOrder = 0;

        foreach ($images as $index => $image) {
            try {

                $fileId = PropertyImageUploader::uploadImage($image, Auth::user());
                
                $isPrimary = ($primaryIndex !== null) 
                    ? ($index == $primaryIndex) 
                    : ($sortOrder === 0); 

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
            }
        }

        if (!empty($pivotData)) {
            $property->images()->attach($pivotData);
        } else {
            Log::warning('No images were attached to property', [
                'property_id' => $property->id
            ]);
        }
    }
    
    public function update(
        Property $property,
        array $newImages = [],
        array $deleteFileIds = [],
        ?int $primaryFileId = null,
        array $imageTypes = []
    ): void {
        if (!empty($deleteFileIds)) {
            $this->deleteImages($property, $deleteFileIds);
        }

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

        if ($primaryFileId) {
            $this->setPrimaryImage($property, $primaryFileId);
        }
    }
    
    protected function deleteImages(Property $property, array $fileIds): void
    {
        $propertyImages = PropertyImage::where('property_id', $property->id)
            ->whereIn('file_id', $fileIds)
            ->get();

        $property->images()->detach($fileIds);

        foreach ($fileIds as $fileId) {
            $file = File::find($fileId);

            if ($file) {
    
                $stillInUse = PropertyImage::where('file_id', $fileId)
                    ->where('property_id', '!=', $property->id)
                    ->exists();

                if (!$stillInUse) {
        
                    if ($file->path && Storage::disk($file->disk)->exists($file->path)) {
                        Storage::disk($file->disk)->delete($file->path);
                    }

        
                    if ($file->thumbnail_path && Storage::disk($file->disk)->exists($file->thumbnail_path)) {
                        Storage::disk($file->disk)->delete($file->thumbnail_path);
                    }

        
                    $file->delete();
                }
            }
        }
    }
    
    public function deleteForProperty(Property $property, array $fileIds): void
    {
        $this->deleteImages($property, $fileIds);
    }
    
    public function deleteMultiple(array $propertyImageIds): void
    {
        $propertyImages = PropertyImage::whereIn('id', $propertyImageIds)->get();
        
        foreach ($propertyImages as $propertyImage) {
            $property = $propertyImage->property;
            $this->deleteImages($property, [$propertyImage->file_id]);
        }
    }
    
    public function setPrimaryImage(Property $property, int $fileId): void
    {
        Log::info('Setting primary image', [
            'property_id' => $property->id,
            'file_id' => $fileId
        ]);
        
        $exists = DB::table('property_images')
            ->where('property_id', $property->id)
            ->where('file_id', $fileId)
            ->exists();

        if (!$exists) {
            return;
        }

        DB::table('property_images')
            ->where('property_id', $property->id)
            ->update(['is_primary' => false]);

        DB::table('property_images')
            ->where('property_id', $property->id)
            ->where('file_id', $fileId)
            ->update(['is_primary' => true]);
    }
    
    public function reorder(Property $property, array $order): void
    {
        foreach ($order as $fileId => $sortOrder) {
            $property->images()->updateExistingPivot($fileId, ['sort_order' => $sortOrder]);
        }
    }
    
    public function updateImageType(Property $property, int $fileId, string $imageType): void
    {
        $property->images()->updateExistingPivot($fileId, ['image_type' => $imageType]);
    }
    
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
    
    public function getPrimaryImage(Property $property): ?File
    {
        return $property->images()
            ->wherePivot('is_primary', true)
            ->first();
    }
    
    public function getOrderedImages(Property $property)
    {
        return $property->images()
            ->orderByPivot('sort_order', 'asc')
            ->get();
    }
    
    public function getImagesByType(Property $property, string $imageType)
    {
        return $property->images()
            ->wherePivot('image_type', $imageType)
            ->orderByPivot('sort_order', 'asc')
            ->get();
    }
}