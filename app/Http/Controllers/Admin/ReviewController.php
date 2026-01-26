<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ReviewResource;
use App\Models\Review;
use App\Repositories\Contracts\ReviewRepositoryInterface;

class ReviewController extends Controller
{
    public function __construct(protected ReviewRepositoryInterface $reviewRepository)
    {}

    public function index()
    {
        $inquiries = $this->reviewRepository->paginate();

        return Inertia::render('properties-inquiries/Index', [
            'properties' => ReviewResource::collection($inquiries),
        ]);
    }


    public function show(Review $review)
    {
        return Inertia::render('properties-inquiries/Show', [
            'property' => $review,
        ]);
    }

}
