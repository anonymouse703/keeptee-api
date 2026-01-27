<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\PropertyInquiryResource;
use App\Models\PropertyInquiry;
use App\Repositories\Contracts\PropertyInquiryRepositoryInterface;

class PropertyInquiryController extends Controller
{
    public function __construct(protected PropertyInquiryRepositoryInterface $propertyInquiryRepository)
    {}

    public function index()
    {
        $inquiries = $this->propertyInquiryRepository->paginate();

        return Inertia::render('properties-inquiries/Index', [
            'properties' => PropertyInquiryResource::collection($inquiries),
        ]);
    }


    public function show(PropertyInquiry $inquiry)
    {
        return Inertia::render('properties-inquiries/Show', [
            'property' => $inquiry,
        ]);
    }

    public function setPendingStatus(PropertyInquiry $inquiry)
    {
        return $this->updateStatus($inquiry, 'pending');
    }
    public function setApprovedStatus(PropertyInquiry $inquiry)
    {
        return $this->updateStatus($inquiry, 'approved');
    }
    public function setCancelledStatus(PropertyInquiry $inquiry)       
    {
        return $this->updateStatus($inquiry, 'cancelled');
    }

    private function updateStatus(PropertyInquiry $inquiry, string $status)
    {
        $inquiry->update(['status' => $status]);

        return redirect()
            ->route('properties-inquiries.index')
            ->with('flash', [
                'type' => 'success',
                'message' => __('Inquiry status updated to :status.', ['status' => ucfirst($status)]),
            ]);
    }
}
