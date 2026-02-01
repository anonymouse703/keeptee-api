<?php

namespace App\Enums\Property;

enum ImageType: string
{
    case Exterior = 'exterior';
    case Interior = 'interior';
    case Kitchen = 'kitchen';
    case Bathroom = 'bathroom';
    case Bedroom = 'bedroom';
    case FloorPlan = 'floor_plan';
    case Amenity = 'amenity';
    case Other = 'other';

    public function label(): string
    {
        return match($this) {
            self::Exterior => 'Exterior',
            self::Interior => 'Interior',
            self::Kitchen => 'Kitchen',
            self::Bathroom => 'Bathroom',
            self::Bedroom => 'Bedroom',
            self::FloorPlan => 'Floor Plan',
            self::Amenity => 'Amenity',
            self::Other => 'Other',
        };
    }
}