<?php

namespace App\Services\LeaseFile;

use App\Models\File;
use App\Models\Lease;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\FileUploader\Uploaders\LeaseFilesUploader;

class LeaseFileService
{   
    /**
     * Store lease files using pivot table approach
     * 
     * @param Lease $lease
     * @param array $files Array of UploadedFile objects
     * @param array $documentTypes Array of document types corresponding to each file
     */
    public function store(Lease $lease, array $files, array $documentTypes = []): void
    {
        ksort($files);
        ksort($documentTypes);

        $pivotData = [];
        $uploadedCount = 0;
        $failedCount = 0;
        $failedFiles = [];

        foreach ($files as $index => $uploadedFile) {
            try {
                // Upload file and get file ID
                $fileId = LeaseFilesUploader::uploadFile($uploadedFile, Auth::user());

                if (!$fileId) {
                    throw new \Exception('File upload returned null file ID');
                }

                // Get document type for this file
                $documentType = $documentTypes[$index] ?? 'other';

                // Prepare pivot data for lease_files table
                $pivotData[$fileId] = [
                    'document_type' => $documentType,
                ];

                $uploadedCount++;

            } catch (\Exception $e) {
                $failedCount++;
                $failedFiles[] = $uploadedFile->getClientOriginalName();
            }
        }

        // Attach files to lease via lease_files pivot table
        if (!empty($pivotData)) {
            try {
                $lease->files()->attach($pivotData);
            } catch (\Exception $e) {
                throw $e;
            }
        }

        if ($failedCount > 0 && $uploadedCount === 0) {
            throw new \Exception("All file uploads failed. Check logs for details.");
        }

        if ($failedCount > 0) {
            Log::warning('Some files failed to upload', [
                'lease_id' => $lease->id,
                'failed_files' => $failedFiles,
            ]);
        }
    }
    
    /**
     * Update lease files
     */
    public function update(
        Lease $lease,
        array $newFiles = [],
        array $deleteFileIds = [],
        array $documentTypes = []
    ): void {
        // Delete specified files first
        if (!empty($deleteFileIds)) {
            $this->deleteFiles($lease, $deleteFileIds);
        }

        // Upload new files
        if (!empty($newFiles)) {
            ksort($newFiles);
            ksort($documentTypes);
            
            $pivotData = [];
            $uploadedCount = 0;
            $failedCount = 0;

            foreach ($newFiles as $index => $uploadedFile) {
                try {
                    $fileId = LeaseFilesUploader::uploadFile($uploadedFile, Auth::user());

                    if (!$fileId) {
                        throw new \Exception('File upload returned null file ID');
                    }

                    $documentType = $documentTypes[$index] ?? 'other';

                    $pivotData[$fileId] = [
                        'document_type' => $documentType,
                    ];

                    $uploadedCount++;

                } catch (\Exception $e) {
                    $failedCount++;
                }
            }

            if ($failedCount > 0 && $uploadedCount === 0) {
                throw new \Exception("All file uploads failed during update. Check logs for details.");
            }
        }
    }
    
    /**
     * Delete files from lease
     */
    protected function deleteFiles(Lease $lease, array $fileIds): void
    {
        if (empty($fileIds)) {
            return;
        }

        try {
            // Detach from pivot table
            $lease->files()->detach($fileIds);

            // Delete physical files and File records
            foreach ($fileIds as $fileId) {
                $file = File::find($fileId);

                if ($file) {
                    // Delete physical file
                    if ($file->path && Storage::disk($file->disk)->exists($file->path)) {
                        Storage::disk($file->disk)->delete($file->path);
                    }

                    // Delete thumbnail if exists
                    if ($file->thumbnail_path && Storage::disk($file->disk)->exists($file->thumbnail_path)) {
                        Storage::disk($file->disk)->delete($file->thumbnail_path);
                    }

                    // Delete File record
                    $file->delete();
                } else {
                    Log::warning('File not found for deletion', [
                        'file_id' => $fileId,
                        'lease_id' => $lease->id,
                    ]);
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Public API for deleting files for a specific lease
     */
    public function deleteForLease(Lease $lease, array $fileIds): void
    {
        $this->deleteFiles($lease, $fileIds);
    }
    
    /**
     * Update document type for a file
     */
    public function updateDocumentType(Lease $lease, int $fileId, string $documentType): void
    {
        try {
            $lease->files()->updateExistingPivot($fileId, [
                'document_type' => $documentType
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Get files by document type
     */
    public function getFilesByType(Lease $lease, string $documentType)
    {
        return $lease->files()
            ->wherePivot('document_type', $documentType)
            ->get();
    }
}