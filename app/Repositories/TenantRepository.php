<?php

namespace App\Repositories;

use App\Models\Tenant;
use App\Repositories\Contracts\TenantRepositoryInterface;

class TenantRepository extends BaseRepository implements TenantRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(app(Tenant::class));
    }
}
