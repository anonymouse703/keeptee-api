<?php

namespace App\Repositories;

use App\Models\Amenity;
use App\Repositories\Contracts\AmenityRepositoryInterface;

class AmenityRepository extends BaseRepository implements AmenityRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(app(Amenity::class));
    }
}
