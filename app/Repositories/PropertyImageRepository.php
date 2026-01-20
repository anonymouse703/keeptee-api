<?php

namespace App\Repositories;

use App\Models\PropertyImage;
use App\Repositories\Contracts\PropertyImageRepositoryInterface;

class PropertyImageRepository extends BaseRepository implements PropertyImageRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(app(PropertyImage::class));
    }
}
