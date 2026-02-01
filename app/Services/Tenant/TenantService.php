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
            $fileData = $payload['file_data'] ?? [];
            unset($payload['file_data']);
            
            $tenant = new Tenant();
            $tenant->forceFill($payload);
            $this->tenantRepository->save($tenant);

            if (!empty($fileData)) {
                $tenant->files()->attach($fileData);
            }

            if (!empty($payload['property_id'])) {
                Property::where('id', $payload['property_id'])
                    ->update(['status' => Status::Rented]);
            }

            return $tenant;
        });
    }

    public function update(Tenant $tenant, array $payload): Tenant
    {
        return DB::transaction(function () use ($tenant, $payload) {
            $oldPropertyId = $tenant->property_id;
            $newPropertyId = $payload['property_id'] ?? $oldPropertyId;
            
            $tenant->forceFill($payload);
            $this->tenantRepository->save($tenant);

            if ($oldPropertyId != $newPropertyId) {
                if ($oldPropertyId) {
                    Property::where('id', $oldPropertyId)
                        ->update(['status' => Status::ForRent]);
                }
                
                if ($newPropertyId) {
                    Property::where('id', $newPropertyId)
                        ->update(['status' => Status::Rented]);
                }
            }

            return $tenant;
        });
    }

    public function delete(Tenant $tenant): bool
    {
        return DB::transaction(function () use ($tenant) {
            $propertyId = $tenant->property_id;
            
            $deleted = $tenant->delete();
        
            if ($deleted && $propertyId) {
                Property::where('id', $propertyId)
                    ->update(['status' => Status::ForRent]);
            }
            
            return $deleted;
        });
    }
}