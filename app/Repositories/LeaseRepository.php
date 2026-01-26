<?php

namespace App\Repositories;

use App\Models\Lease;
use App\Repositories\Contracts\LeaseRepositoryInterface;

class LeaseRepository extends BaseRepository implements LeaseRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(app(Lease::class));
    }
}
