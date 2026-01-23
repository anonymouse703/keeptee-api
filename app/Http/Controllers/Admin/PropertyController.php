<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Inertia\Inertia;
use App\Models\Property;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\PropertyResource;
use App\Http\Requests\Admin\Property\StoreRequest;
use App\Http\Requests\Admin\Property\UpdateRequest;
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
        ]);
    }

    public function create()
    {
        return Inertia::render('tags/Create');
    }

    public function store(StoreRequest $request)
    {
        $payload = $request->validated();

     $user = new Property();
     $user->forceFill($payload);

        try {
            $this->propertyRepository->save($user);
        } catch (Exception $exception) {
            report($exception);
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
        return Inertia::render('properties/Show', [
            'property' => $property,
        ]);
    }

    public function edit(Property $property)
    {
        return Inertia::render('properties/Edit', [
            'property' => new PropertyResource($property),
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

    public function toggleStatus(Property $property)
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
}
