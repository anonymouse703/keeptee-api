<?php

namespace App\Services\Tenant;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\TenantFile\TenantFileService;

class TenantService
{
    public function __construct(
        protected TenantFileService $tenantService
    ) {}

    /**
     * Create a new tenant with files
     */
    public function create(array $data): Tenant
    {
        DB::beginTransaction();

        try {
            $tenantData = $this->extractPropertyData($data);
            $files = $this->extractFiles($data);
            $fileTypes = $this->extractFileTypes($data);

            $tenant = new Tenant();
            $tenant->fill($tenantData);
            $tenant->save();

            if (!empty($files)) {
                $this->tenantService->store($tenant, $files, $fileTypes);
            }

            DB::commit();

            return $tenant;
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * Update an existing tenant
     */
    public function update(Tenant $tenant, array $data): Tenant
    {
        DB::beginTransaction();

        try {
            $tenantData = $this->extractPropertyData($data);
            $tenant->fill($tenantData);
            $tenant->save();

            if (isset($data['files']) || isset($data['delete_files'])) {
                $this->handleFileUpdates($tenant, $data);
            }

            DB::commit();

            return $tenant->fresh();
        } catch (\Exception $e) {

            DB::rollBack();
            
            throw $e;
        }
    }

    protected function extractPropertyData(array $data): array
    {
        return collect($data)
            ->except(array_filter(array_keys($data), function ($key) {
                return str_starts_with($key, 'files')
                    || str_starts_with($key, 'file_types')
                    || in_array($key, [ 
                        'delete_files', 
                        'update_files'
                    ]);
            }))
            ->toArray();
    }

    protected function extractFiles(array $data): array
    {
        $images = [];

        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'tenants_') && $value instanceof \Illuminate\Http\UploadedFile) {
                $index = (int) str_replace('tenants_', '', $key);
                $images[$index] = $value;
            }
        }

        return $images;
    }

    protected function extractFileTypes(array $data): array
    {
        $fileTypes = [];

        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'file_types_')) {
                $index = (int) str_replace('file_types_', '', $key);
                $fileTypes[$index] = $value;
            }
        }

        return $fileTypes;
    }

    /**
     * Handle file updates
     */
    protected function handleFileUpdates(Tenant $tenant, array $data): void
    {
        if (!empty($data['delete_files'])) {
            $fileIds = array_map('intval', (array) $data['delete_files']);
            
            $this->tenantService->deleteForFile($tenant, $fileIds);
        }

        $newFiles = [];
        $fileTypes = [];

        if (!empty($data['files']) && is_array($data['files'])) {
            $newFiles = $data['files'];
            $fileTypes = $data['file_types'] ?? [];
        } else {
            $newFiles = $this->extractFiles($data);
            $fileTypes = $this->extractFileTypes($data);
        }

        if (!empty($newFiles)) {
            Log::info('Adding new images', [
                'tenant_id' => $tenant->id,
                'count' => count($newFiles)
            ]);
            
            $this->tenantService->update(
                $tenant,
                $newFiles,
                [],
                null,
                $fileTypes
            );
        }

        if (!empty($data['update_files'])) {
            foreach ($data['update_files'] as $fileUpdate) {

                if (isset($fileUpdate['file_type'])) {
                    $this->tenantService->updateFileType(
                        $tenant,
                        $fileUpdate['id'],
                        $fileUpdate['file_type']
                    );
                }
            }
        }
    }

}