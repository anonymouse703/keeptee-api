<?php

namespace App\Repositories;

use App\Models\Property;
use App\Repositories\Contracts\PropertyRepositoryInterface;

class PropertyRepository extends BaseRepository implements PropertyRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(app(Property::class));
    }
}
