<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Inertia\Inertia;
use App\Models\Lease;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\LeaseResource;
use App\Http\Requests\Admin\Lease\StoreRequest;
use App\Http\Requests\Admin\Lease\UpdateRequest;
use App\Repositories\Contracts\LeaseRepositoryInterface;

class LeaseController extends Controller
{
    public function __construct(protected LeaseRepositoryInterface $leaseRepository)
    {}

    public function index()
    {
        $leases = $this->leaseRepository->paginate();

        return Inertia::render('leases/Index', [
            'leases' => LeaseResource::collection($leases),
        ]);
    }

    public function create()
    {
        return Inertia::render('leases/Create');
    }

    public function store(StoreRequest $request)
    {
        $payload = $request->validated();

        $lease = new Lease();
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
                'message' => __('Lease successfully created.'),
            ]);
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
