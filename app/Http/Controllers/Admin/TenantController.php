<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Tenant\DocumentType;
use Exception;
use Inertia\Inertia;
use App\Models\Tenant;
use App\Models\Property;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Tenant\TenantService;
use App\Http\Resources\Admin\TenantResource;
use App\Http\Requests\Admin\Tenant\StoreRequest;
use App\Http\Requests\Admin\Tenant\UpdateRequest;
use App\Repositories\Contracts\TenantRepositoryInterface;

class TenantController extends Controller
{
    public function __construct(protected TenantRepositoryInterface $tenantRepository, protected TenantService $tenantService)
    {}

    public function index()
    {
        $tenants = $this->tenantRepository
                    ->with(['files'])
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
                'document_types' => DocumentType::collection()
            ]);
    }

    public function store(StoreRequest $request)
    {   
        try {
            $this->tenantService->create($request->all());

            return redirect()
                ->route('tenants.index')
                ->with('flash', [
                    'type' => 'success',
                    'message' => __('Tenant successfully created.'),
                ]);

        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors(__('Failed to create tenant: ' . $exception->getMessage()));
        }
    }

    public function show(Tenant $tenant)
    {
        $tenant->load(['files']);
        return Inertia::render('tenants/Show', [
            'tenant' => new TenantResource($tenant),
        ]);
    }
    
    public function edit(Tenant $tenant)
    {
        $tenant->load(['property', 'files']);
        
        return Inertia::render('tenants/Edit', [
            'tenant' => new TenantResource($tenant),
            'properties' => Property::pluck('title', 'id'),
            'document_types' => \App\Enums\Tenant\DocumentType::collection(),
        ]);
    }

    public function update(UpdateRequest $request, Tenant $tenant)
    {
        try {
            $this->tenantService->update($tenant, $request->all());

            return redirect()
                ->route('tenants.index')
                ->with('flash', [
                    'type' => 'success',
                    'message' => __('Tenant successfully updated.'),
                ]);

        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors(__('Failed to update tenant: ' . $exception->getMessage()));
        }
    }

    public function destroy(Tenant $tenant)
    {
        try {
            $this->tenantService->delete($tenant);
            
            return redirect()
                ->route('tenants.index')
                ->with('flash', [
                    'type' => 'success',
                    'message' => __('Tenant successfully deleted.'),
                ]);
                
        } catch (Exception $exception) {
            report($exception);
            
            return back()->with('flash', [
                'type' => 'error',
                'message' => __('Failed to delete tenant.'),
            ]);
        }
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
