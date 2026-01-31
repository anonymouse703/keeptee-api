<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Inertia\Inertia;
use App\Models\RentPayment;
use Illuminate\Http\Request;
use App\Enums\RentPayment\Status;
use App\Http\Controllers\Controller;
use App\Enums\RentPayment\PaymentMethod;
use App\Http\Resources\Admin\RentPaymentResource;
use App\Http\Requests\Admin\RentPayment\StoreRequest;
use App\Http\Requests\Admin\RentPayment\UpdateRequest;
use App\Repositories\Contracts\RentPaymentRepositoryInterface;

class RentPaymentController extends Controller
{
    public function __construct(protected RentPaymentRepositoryInterface $rentPaymentRepository)
    {}

    public function index()
    {
        $rentalPayments = $this->rentPaymentRepository
                            ->with(['lease.tenant'])
                            ->paginate();

        return Inertia::render('rent-payments/Index', [
            'rentalPayments' => RentPaymentResource::collection($rentalPayments),
        ]);
    }

    public function create()
    {
        return Inertia::render('rent-payments/Create', [
            'payment_method' => PaymentMethod::collection(),
            'status' => Status::collection(),
        ]);
    }

    public function store(StoreRequest $request)
    {
        $payload = $request->validated();

        $rentalPayment = new RentPayment();
        $rentalPayment->forceFill($payload);

        try {
            $this->rentPaymentRepository->save($rentalPayment);
        } catch (Exception $exception) {
            report($exception);
        }

        return redirect()
            ->route('rent-payments.index')
            ->with('flash', [
                'type' => 'success',
                'message' => __('Rental payment successfully created.'),
            ]);
    }

    public function edit(RentPayment $rentalPayment)
    {
        return Inertia::render('rent-payments/Edit', [
            'rentalPayment' => new RentPaymentResource($rentalPayment),
        ]);
    }


    public function update(UpdateRequest $request, RentPayment $rentalPayment)
    {
        $payload = $request->validated();

        $rentalPayment->forceFill($payload);
        try {
            $this->rentPaymentRepository->save($rentalPayment);
        } catch (Exception $exception) {
            report($exception);
        }

        return redirect()
            ->route('rent-payments.index')
            ->with('flash', [
                'type' => 'success',
                'message' => __('Rental payment successfully updated.'),
            ]);
    }

    public function destroy(RentPayment $rentalPayment)
    {
        try {
            $this->rentPaymentRepository->delete($rentalPayment);
        } catch (Exception $exception) {
            report($exception);
        }

        return redirect()
            ->route('rent-payments.index')
            ->with('flash', [
                'type' => 'danger',
                'message' => __('Rental payment successfully deleted.'),
            ]);
    }   

    public function setPendingStatus(RentPayment $rentalPayment)
    {
        return $this->updateStatus($rentalPayment, 'pending');
    }

    public function setPaidStatus(RentPayment $rentalPayment)
    {
        return $this->updateStatus($rentalPayment, 'paid');
    }

    public function setOverdueStatus(RentPayment $rentalPayment)
    {
        return $this->updateStatus($rentalPayment, 'overdue');
    }

    private function updateStatus(RentPayment $rentalPayment, string $status)
    {
        $rentalPayment->update(['status' => $status]);

        return redirect()
            ->route('rent-payments.index')
            ->with('flash', [
                'type' => 'success',
                'message' => __('Rental payment status updated to :status.', ['status' => ucfirst($status)]),
            ]);
    }   

    public function searchTenant(Request $request)
    {
        $query = (string) $request->query('query', '');

        if ($query === '') {
            return response()->json([]);
        }

        $properties = $this->rentPaymentRepository
            ->searchByKey($query)
            ->searchByActive()
            ->paginate();

        return response()->json($properties);
    }
}
