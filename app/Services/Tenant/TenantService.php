<?php

namespace App\Services\Tenant;

use App\Models\Tenant;
use App\Models\Property;
use App\Enums\Property\Status;
use Illuminate\Support\Facades\DB;
use App\Repositories\TenantRepository;

class TenantService
{
    public function __construct(
        protected TenantRepository $tenantRepository
    ) {}

    public function create(array $payload): Tenant
    {
        return DB::transaction(function () use ($payload) {
            $tenant = new Tenant();
            $tenant->forceFill($payload);
            $this->tenantRepository->save($tenant);

            if (! empty($payload['property_id'])) {
                Property::where('id', $payload['property_id'])
                    ->update(['status' => Status::Rented]);
            }

            return $tenant;
        });
    }
}
