<?php

namespace App\Repositories;

use App\Models\Maintenance;
use App\Repositories\Contracts\MaintenanceRepositoryInterface;

class MaintenanceRepository extends BaseRepository implements MaintenanceRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(app(Maintenance::class));
    }
}
