<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Tag;
use Inertia\Inertia;
use Nette\Utils\Image;
use App\Models\Amenity;
use App\Models\Property;
use Illuminate\Http\Request;
use App\Models\PropertyImage;
use App\Enums\Property\Status;
use App\Enums\Property\ImageType;
use Illuminate\Support\Facades\DB;
use App\Enums\Property\PropertyType;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\Property\PropertyService;
use App\Http\Resources\Admin\PropertyResource;
use App\Http\Requests\Admin\Property\StoreRequest;
use App\Http\Requests\Admin\Property\StatusRequest;
use App\Http\Requests\Admin\Property\UpdateRequest;
use App\Services\PropertyImages\PropertyImageService;
use App\Repositories\Contracts\PropertyRepositoryInterface;

class PropertyController extends Controller
{
    public function __construct(protected PropertyRepositoryInterface $propertyRepository,  protected PropertyService $propertyService)
    {}

    public function index()
    {
        $properties = $this->propertyRepository->paginate();

        return Inertia::render('properties/Index', [
            'properties' => PropertyResource::collection($properties),
            'statuses' => Status::collection(),
        ]);
    }

    public function create()
    {
        return Inertia::render('properties/Create', [
            'property_types' => PropertyType::collection(),
            'statuses' => Status::collection(),
            'image_types' => ImageType::collection(),
            'amenities' => Amenity::pluck('id', 'name'),
            'tags' => Tag::pluck('id','name')
        ]);
    }

    public function store(StoreRequest $request)
    {
        try {
            $this->propertyService->create($request->all());

            return redirect()
                ->route('properties.index')
                ->with('flash', [
                    'type' => 'success',
                    'message' => __('Property successfully created.'),
                ]);

        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors(__('Failed to create property: ' . $exception->getMessage()));
        }
    }


    public function show(Property $property)
    {
        $property->load(['images', 'owner']);

        return Inertia::render('properties/Show', [
            'property' => new PropertyResource($property),
        ]);
    }

    public function edit(Property $property)
    {
        $property->load(['images', 'tags', 'amenities','owner']);
        
        return Inertia::render('properties/Edit', [
            'property' => new PropertyResource($property),
            'property_types' => PropertyType::collection(),
            'statuses' => Status::collection(),
            'image_types' => ImageType::collection(),
            'amenities' => Amenity::pluck('id', 'name'),
            'tags' => Tag::pluck('id','name')
        ]);
    }

    public function update(UpdateRequest $request, Property $property)
    {
        try {
            $this->propertyService->update($property, $request->all());

            return redirect()
                ->route('properties.index')
                ->with('flash', [
                    'type' => 'success',
                    'message' => __('Property successfully updated.'),
                ]);

        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors(__('Failed to update property: ' . $exception->getMessage()));
        }
    }

    public function destroy(Property $property)
    {
        try {
            $this->propertyRepository->delete($property);
        } catch (Exception $exception) {
            report($exception);
        }

        return redirect()
            ->route('properties.index')
            ->with('flash', [
                'type' => 'danger',
                'message' => __('Property successfully deleted.'),
            ]);
    }

    public function setFeatured(Property $property)
    {
     $property->update([
            'is_featured' => ! $property->is_featured,
        ]);

         return redirect()
            ->route('properties.index')
            ->with('flash', [
                'type' => 'success', 
                'message' => __('Property featured status updated.'), 
            ]);
    }
    
    public function toggleActiveStatus(Property $property)
    {
     $property->update([
            'is_active' => ! $property->is_active,
        ]);

         return redirect()
            ->route('properties.index')
            ->with('flash', [
                'type' => 'success', 
                'message' => __('Property status updated.'), 
            ]);
    }

    public function updateStatus(Property $property, StatusRequest $request)
    {
        $property->update($request->validated());

        return redirect()
            ->route('properties.index')
            ->with('flash', [
                'type' => 'success', 
                'message' => __('Property status updated successfully.'), 
            ]);
    }

    public function searchProperty(Request $request)
    {
        $query = (string) $request->query('query', '');

        if ($query === '') {
            return response()->json([]);
        }

        $properties = $this->propertyRepository
            ->searchByTitle($query)
            ->searchByActive()
            ->searchByForRentable()
            ->limit(10)
            ->get(['id', 'title']);

        return response()->json($properties);
    }

}
