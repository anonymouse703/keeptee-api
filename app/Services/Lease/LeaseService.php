<?php

namespace App\Services\Lease;

use App\Models\Lease;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\LeaseFile\LeaseFileService;
use App\Repositories\Contracts\LeaseRepositoryInterface;

class LeaseService
{
    public function __construct(
        protected LeaseRepositoryInterface $leaseRepository,
        protected LeaseFileService $fileService
    ) {}

    /**
     * Create a new lease with files
     */
    public function create(array $data): Lease
    {
        DB::beginTransaction();

        try {
            // 1. Extract and organize data
            $leaseData = $this->extractLeaseData($data);
            $files = $this->extractFiles($data);
            $documentTypes = $this->extractDocumentTypes($data);

            // 2. Create the lease
            $lease = new Lease();
            $lease->fill($leaseData);
            $this->leaseRepository->save($lease);

            // 3. Attach files
            if (!empty($files)) {
                $this->fileService->store($lease, $files, $documentTypes);
            }

            DB::commit();

            return $lease->fresh(['files']);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing lease
     */
    public function update(Lease $lease, array $data): Lease
    {
        DB::beginTransaction();

        try {
            // 1. Update lease data
            $leaseData = $this->extractLeaseData($data);
            $lease->fill($leaseData);
            $this->leaseRepository->save($lease);

            // 2. Handle file updates
            if (isset($data['files']) || isset($data['delete_files'])) {
                $this->handleFileUpdates($lease, $data);
            }

            DB::commit();

            return $lease->fresh(['files']);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a lease
     */
    public function delete(Lease $lease): bool
    {
        return DB::transaction(function () use ($lease) {
            // Delete all lease files
            $fileIds = $lease->files()->pluck('files.id')->toArray();
            if (!empty($fileIds)) {
                $this->fileService->deleteForLease($lease, $fileIds);
            }
            
            // Delete the lease
            $deleted = $lease->delete();

            return $deleted;
        });
    }

    /**
     * Extract lease-specific data (exclude file-related fields)
     */
    protected function extractLeaseData(array $data): array
    {
        return collect($data)
            ->except(array_filter(array_keys($data), function($key) {
                return str_starts_with($key, 'files') 
                    || str_starts_with($key, 'file_document_types')
                    || in_array($key, ['delete_files', '_method', '_token']);
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
                }
            }
        }

        // Also check for files[0], files[1], etc. pattern
        foreach ($data as $key => $value) {
            if (preg_match('/^files\[(\d+)\]$/', $key, $matches)) {
                $index = (int) $matches[1];
                if ($value instanceof \Illuminate\Http\UploadedFile) {
                    $files[$index] = $value;
                }
            }
        }

        // Sort by index
        ksort($files);

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
        }

        // Also check for file_document_types[0], file_document_types[1], etc. pattern
        foreach ($data as $key => $value) {
            if (preg_match('/^file_document_types\[(\d+)\]$/', $key, $matches)) {
                $index = (int) $matches[1];
                $documentTypes[$index] = $value;
            }
        }

        // Sort by index
        ksort($documentTypes);

        // Fill missing indices with default value
        $maxIndex = !empty($documentTypes) ? max(array_keys($documentTypes)) : -1;
        for ($i = 0; $i <= $maxIndex; $i++) {
            if (!isset($documentTypes[$i])) {
                $documentTypes[$i] = 'other';
            }
        }

        return $documentTypes;
    }

    /**
     * Handle file updates
     */
    protected function handleFileUpdates(Lease $lease, array $data): void
    {
        // Delete files - Frontend sends URLs, we need to convert to file IDs
        if (!empty($data['delete_files'])) {
            $deleteUrls = is_array($data['delete_files']) ? $data['delete_files'] : [$data['delete_files']];
            
            // Convert URLs to file IDs
            $fileIds = [];
            foreach ($deleteUrls as $url) {
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
                } else {
                    Log::warning('Could not resolve URL to file ID', [
                        'url' => $url,
                        'lease_id' => $lease->id,
                    ]);
                }
            }
            
            if (!empty($fileIds)) {
                $this->fileService->deleteForLease($lease, $fileIds);
            }
        }

        // Add new files
        $newFiles = $this->extractFiles($data);
        $documentTypes = $this->extractDocumentTypes($data);

        if (!empty($newFiles)) {
            $this->fileService->update(
                $lease,
                $newFiles,
                [],
                $documentTypes
            );
        }
    }
}