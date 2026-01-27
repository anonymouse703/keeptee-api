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
        $reviews = $this->reviewRepository->paginate();

        return Inertia::render('reviews/Index', [
            'reviews' => ReviewResource::collection($reviews),
        ]);
    }


    public function show(Review $review)
    {
        return Inertia::render('reviews/Show', [
            'review' => $review,
        ]);
    }

}
