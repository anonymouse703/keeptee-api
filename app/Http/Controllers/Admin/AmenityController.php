<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Inertia\Inertia;
use App\Models\Amenity;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AmenityResource;
use App\Http\Requests\Admin\Amenity\StoreRequest;
use App\Http\Requests\Admin\Amenity\UpdateRequest;
use App\Repositories\Contracts\AmenityRepositoryInterface;

class AmenityController extends Controller
{
    public function __construct(protected AmenityRepositoryInterface $amenityRepository)
    {}

    public function index()
    {
        $amenities = $this->amenityRepository->paginate();

        return Inertia::render('amenities/Index', [
            'amenities' => AmenityResource::collection($amenities),
        ]);
    }

    public function create()
    {
        return Inertia::render('amenities/Create');
    }

    public function store(StoreRequest $request)
    {
        $payload = $request->validated();

        $tag = new Amenity();
        $tag->forceFill($payload);

        try {
            $this->amenityRepository->save($tag);
        } catch (Exception $exception) {
            report($exception);
        }

        return redirect()
            ->route('amenities.index')
            ->with('flash', [
                'type' => 'success',
                'message' => __('Amenity successfully created.'),
            ]);
    }

    public function edit(Amenity $amenity)
    {
        return Inertia::render('amenities/Edit', [
            'amenity' => new AmenityResource($amenity),
        ]);
    }


    public function update(UpdateRequest $request, Amenity $amenity)
    {
        $payload = $request->validated();

        $amenity->forceFill($payload);

        try {
            $this->amenityRepository->save($amenity);
        } catch (Exception $exception) {
            report($exception);
        }

        return redirect()
            ->route('amenities.index')
            ->with('flash', [
                'type' => 'success',
                'message' => __('Amenity successfully updated.'),
            ]);
    }

    public function destroy(Amenity $amenity)
    {
        try {
            $this->amenityRepository->delete($amenity);
        } catch (Exception $exception) {
            report($exception);
        }

        return redirect()
            ->route('amenities.index')
            ->with('flash', [
                'type' => 'danger',
                'message' => __('Amenity successfully deleted.'),
            ]);
    }

}
