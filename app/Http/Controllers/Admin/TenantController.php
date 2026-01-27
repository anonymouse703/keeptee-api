<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Inertia\Inertia;
use App\Models\Tenant;
use App\Models\Property;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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
        $tenants = $this->tenantRepository->paginate();

        return Inertia::render('tenants/Index', [
            'tenants' => TenantResource::collection($tenants),
        ]);
    }

    public function create()
    {
        $properties = Property::pluck('title', 'id');
        return Inertia::render('tenants/Create', 
            [
                'properties' => $properties,
            ]);
    }

    public function store(StoreRequest $request)
    {
        $payload = $request->validated();

        $tenant = new Tenant();
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
                'message' => __('Tenant successfully created.'),
            ]);
    }

    public function show(Tenant $tenant)
    {
        return Inertia::render('tenants/Show', [
            'tenant' => $tenant,
        ]);
    }

    public function edit(Tenant $tenant)
    {
        return Inertia::render('tenants/Edit', [
            'tenant' => new TenantResource($tenant),
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
}
