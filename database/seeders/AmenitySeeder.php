<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Amenity;

class AmenitySeeder extends Seeder
{
    public function run(): void
    {
        $amenities = [
            ['name' => 'WiFi', 'icon' => 'wifi'],
            ['name' => 'Parking', 'icon' => 'parking'],
            ['name' => 'Swimming Pool', 'icon' => 'pool'],
            ['name' => 'Gym', 'icon' => 'gym'],
            ['name' => 'Air Conditioning', 'icon' => 'ac'],
            ['name' => 'Security', 'icon' => 'security'],
            ['name' => 'Pet Friendly', 'icon' => 'pet'],
            ['name' => 'Balcony', 'icon' => 'balcony'],
            ['name' => 'Garden', 'icon' => 'garden'],
        ];

        foreach ($amenities as $amenity) {
            Amenity::updateOrCreate(
                ['name' => $amenity['name']],
                $amenity
            );
        }
    }
}
