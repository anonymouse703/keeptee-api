<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Amenity;

class AmenitySeeder extends Seeder
{
    public function run(): void
    {
        $amenities = [
            ['name' => 'WiFi'],
            ['name' => 'Parking'],
            ['name' => 'Swimming Pool'],
            ['name' => 'Gym'],
            ['name' => 'Air Conditioning'],
            ['name' => 'Security'],
            ['name' => 'Pet Friendly'],
            ['name' => 'Balcony'],
            ['name' => 'Garden'],
        ];

        foreach ($amenities as $amenity) {
            Amenity::updateOrCreate(
                ['name' => $amenity['name']],
                $amenity
            );
        }
    }
}
