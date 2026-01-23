<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tag;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            [
                'name' => 'New Listing',
                'color' => '#22C55E', // green
                'description' => 'Recently added properties',
                'is_active' => true,
            ],
            [
                'name' => 'Reduced Price',
                'color' => '#F59E0B', // amber
                'description' => 'Properties with a recent price reduction',
                'is_active' => true,
            ],
            [
                'name' => 'Featured',
                'color' => '#3B82F6', // blue
                'description' => 'Highlighted or promoted listings',
                'is_active' => true,
            ],
            [
                'name' => 'Hot Deal',
                'color' => '#EF4444', // red
                'description' => 'Limited-time or high-interest deals',
                'is_active' => true,
            ],
            [
                'name' => 'Luxury',
                'color' => '#8B5CF6', // purple
                'description' => 'High-end luxury properties',
                'is_active' => true,
            ],
        ];

        foreach ($tags as $tag) {
            Tag::updateOrCreate(
                ['name' => $tag['name']], 
                [
                    'color' => $tag['color'],
                    'description' => $tag['description'],
                    'is_active' => $tag['is_active'],
                ]
            );
        }
    }
}
