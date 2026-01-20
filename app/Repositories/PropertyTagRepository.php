<?php

namespace App\Repositories;

use App\Models\PropertyTag;
use App\Repositories\Contracts\PropertyTagRepositoryInterface;

class PropertyTagRepository extends BaseRepository implements PropertyTagRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(app(PropertyTag::class));
    }
}
