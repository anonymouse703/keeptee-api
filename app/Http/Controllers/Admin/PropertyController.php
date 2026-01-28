<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Inertia\Inertia;
use App\Models\Property;
use Illuminate\Http\Request;
use App\Models\PropertyImage;
use App\Enums\Property\Status;
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
        ]);
    }

    public function store(StoreRequest $request, PropertyImageService $imageService)
    {
        DB::beginTransaction();

        try {
            $data = collect($request->validated())
                ->except('images')
                ->toArray();

            $property = new Property();

            $property->forceFill($data);

            $property->owner_id = Auth::id();
            $property->is_active = true;
            $property->is_featured = true;

            $this->propertyRepository->save($property);

            if ($request->hasFile('images')) {
                $imageService->store($property, $request->file('images'));
            }

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            report($exception);

            return back()->withErrors(__('Failed to create property.'));
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
            'property' => $property,
        ]);
    }

    public function edit(Property $property)
    {
        $property->load(['images', 'owner']);
        
        return Inertia::render('properties/Edit', [
            'property' => new PropertyResource($property),
            'property_types' => PropertyType::collection(),
            'statuses' => Status::collection(),
        ]);
    }

    public function update(UpdateRequest $request, Property $property)
    {
        $payload = $request->validated();

        $property->forceFill($payload);

        try {
            $this->propertyRepository->save($property);
        } catch (Exception $exception) {
            report($exception);
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
        $imageService->delete($image);

        return back()->with('flash', [
            'type' => 'success',
            'message' => __('Image deleted successfully.'),
        ]);
    }
}
