<?php

namespace App\Services\TenantFile;

use App\Models\File;
use App\Models\Tenant;
use App\Models\TenantFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\FileUploader\Uploaders\TenantFilesUploader;

class TenantFileService
{   
    /**
     * Store tenant files using pivot table approach
     * 
     * @param Tenant $tenant
     * @param array $files Array of UploadedFile objects
     * @param array $documentTypes Array of document types corresponding to each file
     */
    public function store(Tenant $tenant, array $files, array $documentTypes = []): void
    {
        if (empty($files)) {
            Log::info('No files to store for tenant', ['tenant_id' => $tenant->id]);
            return;
        }

        // Sort files by index if associative array
        ksort($files);
        ksort($documentTypes);

        Log::info('Storing tenant files', [
            'tenant_id' => $tenant->id,
            'files_count' => count($files),
            'document_types_count' => count($documentTypes),
            'file_indices' => array_keys($files),
            'document_type_indices' => array_keys($documentTypes),
        ]);

        $pivotData = [];
        $uploadedCount = 0;
        $failedCount = 0;

        foreach ($files as $index => $uploadedFile) {
            try {
                Log::info('Processing file for upload', [
                    'tenant_id' => $tenant->id,
                    'index' => $index,
                    'filename' => $uploadedFile->getClientOriginalName(),
                    'size' => $uploadedFile->getSize(),
                    'mime' => $uploadedFile->getMimeType(),
                ]);

                // Upload file and get file ID
                $fileId = TenantFilesUploader::uploadFile($uploadedFile, Auth::user());

                if (!$fileId) {
                    throw new \Exception('File upload returned null file ID');
                }

                Log::info('Tenant file uploaded successfully', [
                    'tenant_id' => $tenant->id,
                    'file_id' => $fileId,
                    'index' => $index,
                    'original_name' => $uploadedFile->getClientOriginalName()
                ]);

                // Get document type for this file
                $documentType = $documentTypes[$index] ?? 'other';

                Log::info('Assigning document type', [
                    'file_id' => $fileId,
                    'index' => $index,
                    'document_type' => $documentType,
                ]);

                // Prepare pivot data for tenant_files table
                $pivotData[$fileId] = [
                    'document_type' => $documentType,
                ];

                $uploadedCount++;

            } catch (\Exception $e) {
                $failedCount++;
                
                Log::error('Failed to upload tenant file', [
                    'tenant_id' => $tenant->id,
                    'file_index' => $index,
                    'file_name' => $uploadedFile->getClientOriginalName(),
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                // Don't throw - continue with remaining files
            }
        }

        // Attach files to tenant via tenant_files pivot table
        if (!empty($pivotData)) {
            try {
                $tenant->files()->attach($pivotData);
                
                Log::info('Tenant files attached successfully', [
                    'tenant_id' => $tenant->id,
                    'file_count' => count($pivotData),
                    'file_ids' => array_keys($pivotData),
                    'pivot_data' => $pivotData,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to attach files to tenant', [
                    'tenant_id' => $tenant->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e;
            }
        } else {
            Log::warning('No files were attached to tenant', [
                'tenant_id' => $tenant->id,
                'uploaded_count' => $uploadedCount,
                'failed_count' => $failedCount,
            ]);
            
            if ($failedCount > 0 && $uploadedCount === 0) {
                throw new \Exception("All file uploads failed. Check logs for details.");
            }
        }

        Log::info('File storage complete', [
            'tenant_id' => $tenant->id,
            'uploaded' => $uploadedCount,
            'failed' => $failedCount,
        ]);
    }
    
    /**
     * Update tenant files
     */
    public function update(
        Tenant $tenant,
        array $newFiles = [],
        array $deleteFileIds = [],
        array $documentTypes = []
    ): void {
        Log::info('Updating tenant files', [
            'tenant_id' => $tenant->id,
            'new_files_count' => count($newFiles),
            'delete_file_ids_count' => count($deleteFileIds),
            'document_types_count' => count($documentTypes),
        ]);

        // Delete specified files first
        if (!empty($deleteFileIds)) {
            $this->deleteFiles($tenant, $deleteFileIds);
        }

        // Upload new files
        if (!empty($newFiles)) {
            ksort($newFiles);
            ksort($documentTypes);
            
            $pivotData = [];

            foreach ($newFiles as $index => $uploadedFile) {
                try {
                    Log::info('Uploading new file during update', [
                        'tenant_id' => $tenant->id,
                        'index' => $index,
                        'filename' => $uploadedFile->getClientOriginalName(),
                    ]);

                    $fileId = TenantFilesUploader::uploadFile($uploadedFile, Auth::user());

                    $documentType = $documentTypes[$index] ?? 'other';

                    $pivotData[$fileId] = [
                        'document_type' => $documentType,
                    ];

                    Log::info('File uploaded successfully during update', [
                        'tenant_id' => $tenant->id,
                        'file_id' => $fileId,
                        'document_type' => $documentType,
                    ]);

                } catch (\Exception $e) {
                    Log::error('Failed to upload tenant file during update', [
                        'tenant_id' => $tenant->id,
                        'index' => $index,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if (!empty($pivotData)) {
                $tenant->files()->attach($pivotData);
                
                Log::info('New files attached to tenant', [
                    'tenant_id' => $tenant->id,
                    'file_count' => count($pivotData),
                    'pivot_data' => $pivotData,
                ]);
            }
        }
    }
    
    /**
     * Delete files from tenant
     */
    protected function deleteFiles(Tenant $tenant, array $fileIds): void
    {
        Log::info('Deleting files from tenant', [
            'tenant_id' => $tenant->id,
            'file_ids' => $fileIds,
        ]);

        // Detach from pivot table
        $tenant->files()->detach($fileIds);

        Log::info('Files detached from tenant', [
            'tenant_id' => $tenant->id,
            'file_ids' => $fileIds
        ]);

        // Delete physical files and File records if not used elsewhere
        foreach ($fileIds as $fileId) {
            $file = File::find($fileId);

            if ($file) {
                // Check if file is still used by other tenants
                $stillInUse = TenantFile::where('file_id', $fileId)
                    ->where('tenant_id', '!=', $tenant->id)
                    ->exists();

                if (!$stillInUse) {
                    // Delete physical file
                    if ($file->path && Storage::disk($file->disk)->exists($file->path)) {
                        Storage::disk($file->disk)->delete($file->path);
                        
                        Log::info('Physical file deleted', [
                            'file_id' => $fileId,
                            'path' => $file->path
                        ]);
                    }

                    // Delete thumbnail if exists
                    if ($file->thumbnail_path && Storage::disk($file->disk)->exists($file->thumbnail_path)) {
                        Storage::disk($file->disk)->delete($file->thumbnail_path);
                    }

                    // Delete File record
                    $file->delete();

                    Log::info('File record deleted', [
                        'file_id' => $fileId,
                        'tenant_id' => $tenant->id
                    ]);
                } else {
                    Log::info('File still in use by other tenants', [
                        'file_id' => $fileId
                    ]);
                }
            } else {
                Log::warning('File not found for deletion', [
                    'file_id' => $fileId,
                ]);
            }
        }
    }
    
    /**
     * Public API for deleting files for a specific tenant
     */
    public function deleteForTenant(Tenant $tenant, array $fileIds): void
    {
        $this->deleteFiles($tenant, $fileIds);
    }
    
    /**
     * Update document type for a file
     */
    public function updateDocumentType(Tenant $tenant, int $fileId, string $documentType): void
    {
        $tenant->files()->updateExistingPivot($fileId, ['document_type' => $documentType]);

        Log::info('Document type updated', [
            'tenant_id' => $tenant->id,
            'file_id' => $fileId,
            'document_type' => $documentType
        ]);
    }
    
    /**
     * Get files by document type
     */
    public function getFilesByType(Tenant $tenant, string $documentType)
    {
        return $tenant->files()
            ->wherePivot('document_type', $documentType)
            ->get();
    }
}