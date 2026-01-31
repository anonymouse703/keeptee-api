<?php

namespace App\Http\Controllers\Admin;

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
    public function __construct(protected TenantRepositoryInterface $tenantRepository)
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
        dd($request->all());
        try {
            $tenantService->create($request->validated());
        } catch (Exception $exception) {
            report($exception);

            return back()->with('flash', [
                'type' => 'error',
                'message' => __('Something went wrong. Please try again.'),
            ]);
        }

        return redirect()
            ->route('tenants.index')
            ->with('flash', [
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


    public function update(UpdateRequest $request, Tenant $tenant)
    {
        $payload = $request->validated();

        $tenant->forceFill($payload);
        try {
            $this->tenantRepository->save($tenant);
        } catch (Exception $exception) {
            report($exception);
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
