<?php

namespace App\Services\TenantFile;

use App\Models\File;
use App\Models\Tenant;
use App\Models\TenantFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\FileUploader\Uploaders\TenantFilesUploader;

class TenantFileService
{   
    /**
     * @param Tenant $tenant
     * @param array $files Associative array with indices as keys
     */
    public function store(Tenant $tenant, array $files, ): void
    {
        ksort($files);

        $pivotData = [];

        foreach ($files as $index => $file) {
            try {

                TenantFilesUploader::uploadFile($file, Auth::user());

            } catch (\Exception $e) {
                Log::error('Failed to upload tenant file', [
                    'tenant_id' => $tenant->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        if (!empty($pivotData)) {
            $tenant->files()->attach($pivotData);
        } else {
            Log::warning('No files were attached to tenat', [
                'tenant_id' => $tenant->id
            ]);
        }
    }
    
    public function update(
        Tenant $tenant,
        array $newFiles = [],
        array $deleteFileIds = [],
    ): void {
        if (!empty($deleteFileIds)) {
            $this->deleteFiles($tenant, $deleteFileIds);
        }

        if (!empty($newFiles)) {
            $existingCount = $tenant->files()->count();
            $pivotData = [];

            foreach ($newFiles as $index => $file) {
                try {
                    TenantFilesUploader::uploadFile($file, Auth::user());
                } catch (\Exception $e) {
                    Log::error('Failed to upload property image during update', [
                        'tenant_id' => $tenant->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if (!empty($pivotData)) {
                $tenant->files()->attach($pivotData);
            }
        }
    }
    
    protected function deleteFiles(Tenant $tenant, array $fileIds): void
    {
        TenantFile::where('tenant_id', $tenant->id)
            ->whereIn('file_id', $fileIds)
            ->get();

        $tenant->files()->detach($fileIds);

        foreach ($fileIds as $fileId) {
            $file = File::find($fileId);

            if ($file) {
    
                $stillInUse = TenantFile::where('file_id', $fileId)
                    ->where('tenant_id', '!=', $tenant->id)
                    ->exists();

                if (!$stillInUse) {
        
                    if ($file->path && Storage::disk($file->disk)->exists($file->path)) {
                        Storage::disk($file->disk)->delete($file->path);
                    }

        
                    if ($file->thumbnail_path && Storage::disk($file->disk)->exists($file->thumbnail_path)) {
                        Storage::disk($file->disk)->delete($file->thumbnail_path);
                    }

        
                    $file->delete();
                }
            }
        }
    }
    
    public function deleteForFile(Tenant $tenant, array $fileIds): void
    {
        $this->deleteFiles($tenant, $fileIds);
    }
    
    public function updateFileType(Tenant $tenant, int $fileId, string $fileType): void
    {
        $tenant->files()->updateExistingPivot($fileId, ['document_type' => $fileType]);
    }
    
    
    public function getFilesByType(Tenant $tenant, string $fileType)
    {
        return $tenant->files()
            ->wherePivot('document_type', $fileType)
            ->get();
    }
}