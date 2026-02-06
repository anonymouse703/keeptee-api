<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Lease\DocumentType;
use Exception;
use Inertia\Inertia;
use App\Models\Lease;
use App\Enums\Lease\Status;
use Illuminate\Http\Request;
use App\Enums\Lease\LateFeeType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\LeaseResource;
use App\Http\Requests\Admin\Lease\StoreRequest;
use App\Http\Requests\Admin\Lease\UpdateRequest;
use App\Repositories\Contracts\LeaseRepositoryInterface;
use App\Services\Lease\LeaseService;

class LeaseController extends Controller
{
    public function __construct(protected LeaseRepositoryInterface $leaseRepository,  protected LeaseService $leaseService)
    {}

    public function index()
    {
        $leases = $this->leaseRepository
                ->with(['property', 'tenant'])
                ->paginate();

        return Inertia::render('leases/Index', [
            'leases' => LeaseResource::collection($leases),
        ]);
    }

    public function create()
    {
        return Inertia::render('leases/Create',[
            'statuses' => Status::collection(),
            'late_fee_types' => LateFeeType::collection(),
            'document_types' => DocumentType::collection()
        ]);
    }

    public function store(StoreRequest $request)
    {   
        try {
            $this->leaseService->create($request->all());

            return redirect()
                ->route('leases.index')
                ->with('flash', [
                    'type' => 'success',
                    'message' => __('Lease successfully created.'),
                ]);

        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors(__('Failed to create lease: ' . $exception->getMessage()));
        }
    }

    public function edit(Lease $lease)
    {
        return Inertia::render('leases/Edit', [
            'lease' => new LeaseResource($lease),
        ]);
    }


    public function update(UpdateRequest $request, Lease $lease)
    {
        $payload = $request->validated();

        $lease->forceFill($payload);
        try {
            $this->leaseRepository->save($lease);
        } catch (Exception $exception) {
            report($exception);
        }

        return redirect()
            ->route('leases.index')
            ->with('flash', [
                'type' => 'success',
                'message' => __('Lease successfully updated.'),
            ]);
    }

    public function destroy(Lease $lease)
    {
        try {
            $this->leaseRepository->delete($lease);
        } catch (Exception $exception) {
            report($exception);
        }

        return redirect()
            ->route('leases.index')
            ->with('flash', [
                'type' => 'danger',
                'message' => __('Lease successfully deleted.'),
            ]);
    }

    public function setActiveStatus(Lease $lease)
    {
        return $this->updateStatus($lease, 'active');
    }

    public function setEndedStatus(Lease $lease)
    {
        return $this->updateStatus($lease, 'ended');
    }

    public function setTerminatedStatus(Lease $lease)
    {
        return $this->updateStatus($lease, 'terminated');
    }

    private function updateStatus(Lease $lease, string $status)
    {
        $lease->update(['status' => $status]);

        return redirect()
            ->route('leases.index')
            ->with('flash', [
                'type' => 'success',
                'message' => __('Lease status updated to :status.', ['status' => ucfirst($status)]),
            ]);
    }
}
