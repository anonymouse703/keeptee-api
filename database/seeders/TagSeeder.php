<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tag;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            'New Listing',
            'Reduced Price',
            'Featured',
            'Hot Deal',
            'Luxury',
        ];

        foreach ($tags as $tag) {
            Tag::updateOrCreate(
                ['name' => $tag],
                ['name' => $tag]
            );
        }
    }
}
