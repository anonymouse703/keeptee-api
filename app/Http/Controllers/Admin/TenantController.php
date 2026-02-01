<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\File;
use Inertia\Inertia;
use App\Models\Tenant;
use App\Models\Property;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\Tenant\TenantService;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Admin\TenantResource;
use App\Http\Requests\Admin\Tenant\StoreRequest;
use App\Http\Requests\Admin\Tenant\UpdateRequest;
use App\Repositories\Contracts\FileRepositoryInterface;
use App\Repositories\Contracts\TenantRepositoryInterface;
use App\Services\FileUploader\Uploaders\TenantFilesUploader;

class TenantController extends Controller
{
    public function __construct(protected TenantRepositoryInterface $tenantRepository, protected FileRepositoryInterface $fileRepository)
    {}

    public function index()
    {
        $tenants = $this->tenantRepository
                    ->with(['property'])
                    ->paginate();

        return Inertia::render('tenants/Index', [
            'tenants' => TenantResource::collection($tenants)
        ]);
    }

    public function create()
    {
        return Inertia::render('tenants/Create', 
            [
                'properties' => Property::pluck('title', 'id'),
            ]);
    }

    public function store(StoreRequest $request, TenantService $tenantService)
    {
        try {
            $validatedData = $request->validated();
            $fileIds = [];
            
            if ($request->hasFile('file')) {
                foreach ($request->file('file') as $uploadedFile) {
                    $fileIds[] = TenantFilesUploader::uploadFile(
                        $uploadedFile, 
                       Auth::user()
                    );
                }
                $validatedData['file_ids'] = $fileIds;
                unset($validatedData['file']);
            }
            
            $tenantService->create($validatedData);
            
        } catch (Exception $exception) {
            report($exception);
            return back()->with('flash', [
                'type' => 'error',
                'message' => __('Something went wrong. Please try again.'),
            ]);
        }

        return redirect()->route('tenants.index')->with('flash', [
            'type' => 'success',
            'message' => __('Tenant successfully created.'),
        ]);
    }

    public function show(Tenant $tenant)
    {
        $tenant->load('property');
        
        return Inertia::render('tenants/Show', [
            'tenant' => $tenant,
        ]);
    }

    public function edit(Tenant $tenant)
    {
        $tenant->load('property');
        
        return Inertia::render('tenants/Edit', [
            'tenant' => new TenantResource($tenant),
            'properties' => Property::pluck('title', 'id'),
        ]);
    }


    public function update(UpdateRequest $request, Tenant $tenant, TenantService $tenantService)
    {
        try {
            $validatedData = $request->validated();
            $fileData = [];
            
            // Handle new file uploads
            if ($request->hasFile('file')) {
                $documentTypes = $request->input('document_type', []);
                
                foreach ($request->file('file') as $index => $uploadedFile) {
                    $fileId = TenantFilesUploader::uploadFile(
                        $uploadedFile, 
                        Auth::user()
                    );
                    
                    $fileData[$fileId] = [
                        'document_type' => $documentTypes[$index] ?? null
                    ];
                }
            }
            
            // Handle file deletions
            if ($request->has('delete_files')) {
                $deleteFileIds = $request->input('delete_files', []);
                $tenant->files()->detach($deleteFileIds);
                
                foreach ($deleteFileIds as $fileId) {
                    $file = $this->fileRepository->find($fileId);
                    if ($file && !$file->properties()->exists() && !$file->tenants()->exists()) {
                        Storage::disk($file->disk)->delete($file->path);
                        if ($file->thumbnail_path) {
                            Storage::disk($file->disk)->delete($file->thumbnail_path);
                        }
                        $file->delete();
                    }
                }
            }
            
            // Attach new files
            if (!empty($fileData)) {
                $tenant->files()->attach($fileData);
            }
            
            // Remove file-related data from payload before updating tenant
            unset($validatedData['file'], $validatedData['document_type'], $validatedData['delete_files']);
            
            // Update tenant using service
            $tenantService->update($tenant, $validatedData);
            
        } catch (Exception $exception) {
            report($exception);
            
            return back()
                ->withInput()
                ->with('flash', [
                    'type' => 'error',
                    'message' => __('Something went wrong. Please try again.'),
                ]);
        }

        return redirect()
            ->route('tenants.index')
            ->with('flash', [
                'type' => 'success',
                'message' => __('Tenant successfully updated.'),
            ]);
    }

    public function destroy(Tenant $tenant)
    {
        try {
            $this->tenantRepository->delete($tenant);
        } catch (Exception $exception) {
            report($exception);
        }

        return redirect()
            ->route('tenants.index')
            ->with('flash', [
                'type' => 'danger',
                'message' => __('Tenant successfully deleted.'),
            ]);
    }

    public function searchTenant(Request $request)
    {
        $query = (string) $request->query('query', '');

        if ($query === '') {
            return response()->json([]);
        }

        $tenants = $this->tenantRepository
            ->searchByName($query)
            ->limit(10)
            ->get(['id', 'name']);

        return response()->json($tenants);
    }
}
