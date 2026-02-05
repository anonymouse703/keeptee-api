<?php

namespace App\Services\Tenant;

use App\Models\Tenant;
use App\Models\Property;
use App\Enums\Property\Status;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\TenantRepository;
use App\Services\TenantFile\TenantFileService;

class TenantService
{
    public function __construct(
        protected TenantRepository $tenantRepository,
        protected TenantFileService $fileService
    ) {}

    /**
     * Create a new tenant with files
     */
    public function create(array $data): Tenant
    {
        DB::beginTransaction();

        try {
            // 1. Extract and organize data
            $tenantData = $this->extractTenantData($data);
            $files = $this->extractFiles($data);
            $documentTypes = $this->extractDocumentTypes($data);

            Log::info('Creating tenant', [
                'tenant_data' => $tenantData,
                'files_count' => count($files),
                'document_types_count' => count($documentTypes),
                'document_types' => $documentTypes,
            ]);

            // 2. Create the tenant
            $tenant = new Tenant();
            $tenant->fill($tenantData);
            $this->tenantRepository->save($tenant);

            // 3. Attach files
            if (!empty($files)) {
                $this->fileService->store($tenant, $files, $documentTypes);
            }

            // 4. Update property status
            if (!empty($tenantData['property_id'])) {
                $this->updatePropertyStatus($tenantData['property_id'], Status::Rented);
            }

            DB::commit();

            Log::info('Tenant created successfully', [
                'tenant_id' => $tenant->id,
                'files_count' => count($files),
            ]);

            return $tenant;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create tenant', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
            ]);

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
            $oldPropertyId = $tenant->property_id;
            
            Log::info('Updating tenant', [
                'tenant_id' => $tenant->id,
                'data_keys' => array_keys($data),
                'has_files' => isset($data['files']),
                'has_delete_files' => isset($data['delete_files']),
            ]);
            
            // 1. Update tenant data
            $tenantData = $this->extractTenantData($data);
            $tenant->fill($tenantData);
            $this->tenantRepository->save($tenant);

            // 2. Handle file updates
            if (isset($data['files']) || isset($data['delete_files'])) {
                $this->handleFileUpdates($tenant, $data);
            }

            // 3. Handle property status changes
            $newPropertyId = $tenantData['property_id'] ?? null;
            if ($oldPropertyId != $newPropertyId) {
                if ($oldPropertyId) {
                    $this->updatePropertyStatus($oldPropertyId, Status::ForRent);
                }
                if ($newPropertyId) {
                    $this->updatePropertyStatus($newPropertyId, Status::Rented);
                }
            }

            DB::commit();

            Log::info('Tenant updated successfully', [
                'tenant_id' => $tenant->id,
            ]);

            return $tenant->fresh(['files']);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to update tenant', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Delete a tenant
     */
    public function delete(Tenant $tenant): bool
    {
        return DB::transaction(function () use ($tenant) {
            $propertyId = $tenant->property_id;
            
            // Delete all tenant files
            $fileIds = $tenant->files()->pluck('files.id')->toArray();
            if (!empty($fileIds)) {
                $this->fileService->deleteForTenant($tenant, $fileIds);
            }
            
            $deleted = $tenant->delete();
        
            if ($deleted && $propertyId) {
                $this->updatePropertyStatus($propertyId, Status::ForRent);
            }
            
            Log::info('Tenant deleted successfully', [
                'tenant_id' => $tenant->id,
                'property_id' => $propertyId
            ]);
            
            return $deleted;
        });
    }

    /**
     * Extract tenant-specific data (exclude file-related fields)
     */
    protected function extractTenantData(array $data): array
    {
        return collect($data)
            ->except(array_filter(array_keys($data), function($key) {
                return str_starts_with($key, 'files') 
                    || str_starts_with($key, 'file_document_types')
                    || in_array($key, ['delete_files', '_method']);
            }))
            ->toArray();
    }

    /**
     * Extract uploaded files from data
     */
    protected function extractFiles(array $data): array
    {
        $files = [];

        // Check for 'files' array (from your form submission)
        if (isset($data['files']) && is_array($data['files'])) {
            foreach ($data['files'] as $index => $file) {
                if ($file instanceof \Illuminate\Http\UploadedFile) {
                    $files[$index] = $file;
                    Log::info('Found file in files array', [
                        'index' => $index,
                        'name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                    ]);
                }
            }
        }

        // Also check for files[0], files[1], etc. pattern
        foreach ($data as $key => $value) {
            if (preg_match('/^files\[(\d+)\]$/', $key, $matches)) {
                $index = (int) $matches[1];
                if ($value instanceof \Illuminate\Http\UploadedFile) {
                    $files[$index] = $value;
                    Log::info('Found file in indexed pattern', [
                        'index' => $index,
                        'name' => $value->getClientOriginalName(),
                    ]);
                }
            }
        }

        // Sort by index
        ksort($files);

        Log::info('Extracted files', [
            'count' => count($files),
            'indices' => array_keys($files),
        ]);

        return $files;
    }

    /**
     * Extract document types
     */
    protected function extractDocumentTypes(array $data): array
    {
        $documentTypes = [];

        // Check for 'file_document_types' array
        if (isset($data['file_document_types']) && is_array($data['file_document_types'])) {
            $documentTypes = $data['file_document_types'];
            Log::info('Found document types array', [
                'count' => count($documentTypes),
                'types' => $documentTypes,
            ]);
        }

        // Also check for file_document_types[0], file_document_types[1], etc. pattern
        foreach ($data as $key => $value) {
            if (preg_match('/^file_document_types\[(\d+)\]$/', $key, $matches)) {
                $index = (int) $matches[1];
                $documentTypes[$index] = $value;
                Log::info('Found document type in indexed pattern', [
                    'index' => $index,
                    'type' => $value,
                ]);
            }
        }

        // Sort by index
        ksort($documentTypes);

        // Fill missing indices with default value
        $maxIndex = !empty($documentTypes) ? max(array_keys($documentTypes)) : -1;
        for ($i = 0; $i <= $maxIndex; $i++) {
            if (!isset($documentTypes[$i])) {
                $documentTypes[$i] = 'other';
                Log::warning('Missing document type at index, using default', [
                    'index' => $i,
                ]);
            }
        }

        Log::info('Final extracted document types', [
            'count' => count($documentTypes),
            'types' => $documentTypes,
        ]);

        return $documentTypes;
    }

    /**
     * Handle file updates
     */
    protected function handleFileUpdates(Tenant $tenant, array $data): void
    {
        // Delete files - Frontend sends URLs, we need to convert to file IDs
        if (!empty($data['delete_files'])) {
            $deleteUrls = is_array($data['delete_files']) ? $data['delete_files'] : [$data['delete_files']];
            
            Log::info('Processing delete_files', [
                'tenant_id' => $tenant->id,
                'urls' => $deleteUrls,
            ]);
            
            // Convert URLs to file IDs
            $fileIds = [];
            foreach ($deleteUrls as $url) {
                // Extract file ID from URL or path
                // Assuming URL format like: /storage/tenant-files/123/filename.pdf
                // Or the URL might be the full URL like: http://domain.com/storage/...
                
                // Try to find file by URL/path
                $file = \App\Models\File::where('url', $url)
                    ->orWhere(function($query) use ($url) {
                        // Extract path from URL
                        $path = parse_url($url, PHP_URL_PATH);
                        if ($path) {
                            $path = ltrim($path, '/');
                            // Remove 'storage/' prefix if present
                            $path = preg_replace('#^storage/#', '', $path);
                            $query->where('path', $path);
                        }
                    })
                    ->first();
                
                if ($file) {
                    $fileIds[] = $file->id;
                    Log::info('Resolved URL to file ID', [
                        'url' => $url,
                        'file_id' => $file->id,
                    ]);
                } else {
                    Log::warning('Could not resolve URL to file ID', [
                        'url' => $url,
                    ]);
                }
            }
            
            if (!empty($fileIds)) {
                Log::info('Deleting tenant files', [
                    'tenant_id' => $tenant->id,
                    'file_ids' => $fileIds,
                ]);
                
                $this->fileService->deleteForTenant($tenant, $fileIds);
            }
        }

        // Add new files
        $newFiles = $this->extractFiles($data);
        $documentTypes = $this->extractDocumentTypes($data);

        if (!empty($newFiles)) {
            Log::info('Adding new tenant files', [
                'tenant_id' => $tenant->id,
                'count' => count($newFiles),
                'document_types' => $documentTypes,
            ]);
            
            $this->fileService->update(
                $tenant,
                $newFiles,
                [],
                $documentTypes
            );
        }
    }

    /**
     * Update property status
     */
    protected function updatePropertyStatus(int $propertyId, Status $status): void
    {
        Property::where('id', $propertyId)
            ->update(['status' => $status]);

        Log::info('Property status updated', [
            'property_id' => $propertyId,
            'status' => $status->value
        ]);
    }
}