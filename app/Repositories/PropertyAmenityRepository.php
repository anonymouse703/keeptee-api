<?php

namespace App\Repositories;

use App\Models\PropertyAmenity;
use App\Repositories\Contracts\PropertyAmenityRepositoryInterface;

class PropertyAmenityRepository extends BaseRepository implements PropertyAmenityRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(app(PropertyAmenity::class));
    }
}
