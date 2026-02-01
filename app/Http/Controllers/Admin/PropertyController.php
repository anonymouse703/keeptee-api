<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Inertia\Inertia;
use Nette\Utils\Image;
use App\Models\Property;
use Illuminate\Http\Request;
use App\Models\PropertyImage;
use App\Enums\Property\Status;
use App\Enums\Property\ImageType;
use Illuminate\Support\Facades\DB;
use App\Enums\Property\PropertyType;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Admin\PropertyResource;
use App\Http\Requests\Admin\Property\StoreRequest;
use App\Http\Requests\Admin\Property\StatusRequest;
use App\Http\Requests\Admin\Property\UpdateRequest;
use App\Services\PropertyImages\PropertyImageService;
use App\Repositories\Contracts\PropertyRepositoryInterface;

class PropertyController extends Controller
{
    public function __construct(protected PropertyRepositoryInterface $propertyRepository)
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
        ]);
    }

    public function store(StoreRequest $request, PropertyImageService $imageService)
    {
        DB::beginTransaction();

        try {
            $data = collect($request->validated())
                ->except(['images', 'image_types', 'primary_image_index'])
                ->toArray();

            $property = new Property();
            $property->forceFill($data);
            $property->owner_id = Auth::id();
            $property->is_active = true;
            $property->is_featured = true;

            $this->propertyRepository->save($property);

            // Handle image uploads
            if ($request->hasFile('images')) {
                $imageTypes = $request->input('image_types', []);
                $imageService->store($property, $request->file('images'), $imageTypes);
            }

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            report($exception);

            return back()
                ->withInput()
                ->withErrors(__('Failed to create property.'));
        }

        return redirect()
            ->route('properties.index')
            ->with('flash', [
                'type' => 'success',
                'message' => __('Property successfully created.'),
            ]);
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
        $property->load(['images', 'owner']);
        
        return Inertia::render('properties/Edit', [
            'property' => new PropertyResource($property),
            'property_types' => PropertyType::collection(),
            'statuses' => Status::collection(),
            'image_types' => ImageType::collection(),
        ]);
    }

    public function update(UpdateRequest $request, Property $property, PropertyImageService $imageService)
    {
        DB::beginTransaction();

        try {
            $data = collect($request->validated())
                ->except(['images', 'delete_images', 'primary_image_id'])
                ->toArray();

            $property->forceFill($data);
            $this->propertyRepository->save($property);

            // Handle images
            $imageService->update(
                $property,
                $request->file('images', []),
                $request->input('delete_images', []),
                $request->input('primary_image_id')
            );

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            report($exception);

            return back()
                ->withInput()
                ->withErrors(__('Failed to update property.'));
        }

        return redirect()
            ->route('properties.index')
            ->with('flash', [
                'type' => 'success',
                'message' => __('Property successfully updated.'),
            ]);
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

    public function destroyImage(PropertyImage $image, PropertyImageService $imageService)
    {
        $imageService->deleteMultiple([$image->id]);

        return back()->with('flash', [
            'type' => 'success',
            'message' => __('Image deleted successfully.'),
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
