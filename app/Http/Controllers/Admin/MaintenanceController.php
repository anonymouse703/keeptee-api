<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Inertia\Inertia;
use App\Models\Maintenance;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\MaintenanceResource;
use App\Http\Requests\Admin\Maintenance\StoreRequest;
use App\Http\Requests\Admin\Maintenance\UpdateRequest;
use App\Repositories\Contracts\MaintenanceRepositoryInterface;

class MaintenanceController extends Controller
{
    public function __construct(protected MaintenanceRepositoryInterface $maintenanceRepository)
    {}

    public function index()
    {
        $maintenances = $this->maintenanceRepository->paginate();

        return Inertia::render('maintenances/Index', [
            'maintenances' => MaintenanceResource::collection($maintenances),
        ]);
    }

    public function create()
    {
        return Inertia::render('maintenances/Create');
    }

    public function store(StoreRequest $request)
    {
        $payload = $request->validated();

        $maintenance = new Maintenance();
        $maintenance->forceFill($payload);

        try {
            $this->maintenanceRepository->save($maintenance);
        } catch (Exception $exception) {
            report($exception);
        }

        return redirect()
            ->route('maintenances.index')
            ->with('flash', [
                'type' => 'success',
                'message' => __('Maintenance successfully created.'),
            ]);
    }

    public function edit(Maintenance $maintenance)
    {
        return Inertia::render('maintenances/Edit', [
            'maintenance' => new MaintenanceResource($maintenance),
        ]);
    }


    public function update(UpdateRequest $request, Maintenance $maintenance)
    {
        $payload = $request->validated();

        $maintenance->forceFill($payload);
        try {
            $this->maintenanceRepository->save($maintenance);
        } catch (Exception $exception) {
            report($exception);
        }

        return redirect()
            ->route('maintenances.index')
            ->with('flash', [
                'type' => 'success',
                'message' => __('Maintenance successfully updated.'),
            ]);
    }

    public function destroy(Maintenance $maintenance)
    {
        try {
            $this->maintenanceRepository->delete($maintenance);
        } catch (Exception $exception) {
            report($exception);
        }

        return redirect()
            ->route('maintenances.index')
            ->with('flash', [
                'type' => 'danger',
                'message' => __('Maintenance successfully deleted.'),
            ]);
    }

    public function setPendingStatus(Maintenance $maintenance)
    {
        return $this->updateStatus($maintenance, 'pending');
    }

    public function setInProgressStatus(Maintenance $maintenance)
    {
        return $this->updateStatus($maintenance, 'in_progress');
    }

    public function setCompletedStatus(Maintenance $maintenance)
    {
        return $this->updateStatus($maintenance, 'completed');
    }

    private function updateStatus(Maintenance $maintenance, string $status)
    {
        $maintenance->update(['status' => $status]);

        return redirect()
            ->route('maintenances.index')
            ->with('flash', [
                'type' => 'success',
                'message' => __('Maintenance status updated to :status.', ['status' => ucfirst($status)]),
            ]);
    }
}
