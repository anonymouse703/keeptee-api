<?php

namespace App\Services\Property;

use App\Models\Property;
use App\Services\PropertyImage\PropertyImageService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PropertyService
{
    public function __construct(
        protected PropertyImageService $imageService
    ) {}

    /**
     * Create a new property with images, tags, and amenities
     */
    public function create(array $data): Property
    {
        DB::beginTransaction();

        try {
            $propertyData = $this->extractPropertyData($data);
            $images = $this->extractImages($data);
            $imageTypes = $this->extractImageTypes($data);
            $tags = $this->extractTags($data);
            $amenities = $this->extractAmenities($data);
            $primaryIndex = (int) ($data['primary_image_index'] ?? 0);

            $property = new Property();
            $property->fill($propertyData);
            $property->owner_id = Auth::id();
            $property->save();

            if (!empty($images)) {
                $this->imageService->store($property, $images, $primaryIndex, $imageTypes);
            }

            if (!empty($tags)) {
                $this->attachTags($property, $tags);
            }

            if (!empty($amenities)) {
                $this->attachAmenities($property, $amenities);
            }

            DB::commit();

            return $property;
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * Update an existing property
     */
    public function update(Property $property, array $data): Property
    {
        DB::beginTransaction();

        try {
            $propertyData = $this->extractPropertyData($data);
            $property->fill($propertyData);
            $property->save();

            if (isset($data['images']) || isset($data['delete_images']) || isset($data['primary_image_id'])) {
                $this->handleImageUpdates($property, $data);
            }

            if (isset($data['tags']) || $this->hasTags($data)) {
                $tags = $this->extractTags($data);
                $this->syncTags($property, $tags);
            }

            if (isset($data['amenities']) || $this->hasAmenities($data)) {
                $amenities = $this->extractAmenities($data);
                $this->syncAmenities($property, $amenities);
            }

            DB::commit();

            return $property->fresh();
        } catch (\Exception $e) {

            DB::rollBack();
            
            throw $e;
        }
    }

    protected function extractPropertyData(array $data): array
    {
        return collect($data)
            ->except(array_filter(array_keys($data), function ($key) {
                return str_starts_with($key, 'images')
                    || str_starts_with($key, 'image_types')
                    || str_starts_with($key, 'tags')
                    || str_starts_with($key, 'amenities')
                    || in_array($key, [
                        'primary_image_index', 
                        'primary_image_id',
                        'delete_images', 
                        'update_images'
                    ]);
            }))
            ->toArray();
    }

    protected function extractImages(array $data): array
    {
        $images = [];

        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'images_') && $value instanceof \Illuminate\Http\UploadedFile) {
                $index = (int) str_replace('images_', '', $key);
                $images[$index] = $value;
            }
        }

        return $images;
    }

    protected function extractImageTypes(array $data): array
    {
        $imageTypes = [];

        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'image_types_')) {
                $index = (int) str_replace('image_types_', '', $key);
                $imageTypes[$index] = $value;
            }
        }

        return $imageTypes;
    }

    protected function extractTags(array $data): array
    {
        $tags = [];

        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'tags_')) {
                $tags[] = (int) $value;
            }
        }

        return array_unique($tags);
    }

    protected function extractAmenities(array $data): array
    {
        $amenities = [];

        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'amenities_')) {
                $amenities[] = (int) $value;
            }
        }

        return array_unique($amenities);
    }

    protected function hasTags(array $data): bool
    {
        return !empty(array_filter(array_keys($data), fn($key) => str_starts_with($key, 'tags_')));
    }

    protected function hasAmenities(array $data): bool
    {
        return !empty(array_filter(array_keys($data), fn($key) => str_starts_with($key, 'amenities_')));
    }

    protected function attachTags(Property $property, array $tagIds): void
    {
        if (empty($tagIds)) {
            return;
        }

        $property->tags()->attach($tagIds);
    }

    protected function attachAmenities(Property $property, array $amenityIds): void
    {
        if (empty($amenityIds)) {
            return;
        }

        $property->amenities()->attach($amenityIds);
    }

    protected function syncTags(Property $property, array $tagIds): void
    {
        $property->tags()->sync($tagIds);
    }

    protected function syncAmenities(Property $property, array $amenityIds): void
    {
        $property->amenities()->sync($amenityIds);
    }

    /**
     * Handle image updates
     */
    protected function handleImageUpdates(Property $property, array $data): void
    {
        if (!empty($data['delete_images'])) {
            $fileIds = array_map('intval', (array) $data['delete_images']);
            
            $this->imageService->deleteForProperty($property, $fileIds);
        }

        $newImages = [];
        $imageTypes = [];

        if (!empty($data['images']) && is_array($data['images'])) {
            $newImages = $data['images'];
            $imageTypes = $data['image_types'] ?? [];
        } else {
            $newImages = $this->extractImages($data);
            $imageTypes = $this->extractImageTypes($data);
        }

        if (!empty($newImages)) {
            Log::info('Adding new images', [
                'property_id' => $property->id,
                'count' => count($newImages)
            ]);
            
            $this->imageService->update(
                $property,
                $newImages,
                [],
                null,
                $imageTypes
            );
        }

        if (!empty($data['primary_image_id'])) {

            $fileId = (int) $data['primary_image_id'];
            $this->imageService->setPrimaryImage($property, $fileId);
        } elseif (isset($data['primary_image_index']) && !empty($newImages)) {


            $primaryIndex = (int) $data['primary_image_index'];
            
            $this->setPrimaryImageByIndex($property, $primaryIndex);
        }

        if (!empty($data['update_images'])) {
            foreach ($data['update_images'] as $imageUpdate) {
                if (isset($imageUpdate['sort_order'])) {
                    $this->imageService->reorder($property, [
                        $imageUpdate['id'] => $imageUpdate['sort_order']
                    ]);
                }

                if (isset($imageUpdate['image_type'])) {
                    $this->imageService->updateImageType(
                        $property,
                        $imageUpdate['id'],
                        $imageUpdate['image_type']
                    );
                }

                if (!empty($imageUpdate['is_primary'])) {
                    $this->imageService->setPrimaryImage($property, $imageUpdate['id']);
                }
            }
        }
    }

    protected function setPrimaryImageByIndex(Property $property, int $primaryIndex): void
    {
        $property->load(['images' => function ($query) {
            $query->orderBy('property_images.sort_order', 'asc');
        }]);
        
        $allImages = $property->images;

        if ($primaryIndex < 0 || $primaryIndex >= $allImages->count()) {
            return;
        }

        $imageAtIndex = $allImages[$primaryIndex];
        $fileId = $imageAtIndex->id;

        $this->imageService->setPrimaryImage($property, $fileId);
    }
}