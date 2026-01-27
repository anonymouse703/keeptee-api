<?php

namespace App\Services\PropertyImages;

use App\Models\Property;
use App\Models\PropertyImage;
use Illuminate\Http\UploadedFile;
use App\Services\ImageUploadService;

class PropertyImageService
{
    public function __construct(
        protected ImageUploadService $imageUploadService
    ) {}

    public function store(Property $property, array $images): void
    {
        foreach ($images as $index => $image) {
            /** @var UploadedFile $image */
            $path = $this->imageUploadService->upload($image, 'properties');

            $property->images()->create([
                'image_url'  => $path,        // STORAGE PATH
                'is_primary' => $index === 0,  // first image
                'sort_order' => $index + 1,
            ]);
        }
    }

    public function delete(PropertyImage $image): void
    {
        $this->imageUploadService->delete($image->image_url);

        $image->delete();
    }
}
