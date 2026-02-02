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
use App\Models\Amenity;
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
            'amenities' => Amenity::pluck('id', 'name')
        ]);
    }

    public function store(StoreRequest $request, PropertyImageService $imageService)
    {
        DB::beginTransaction();

        try {
            // 1️⃣ Prepare property data
            $data = collect($request->validated())
                ->except(array_filter(array_keys($request->all()), fn($key) =>
                    str_starts_with($key, 'images') || str_starts_with($key, 'image_types') || $key === 'primary_image_index'
                ))
                ->toArray();

            $property = new Property();
            $property->forceFill($data);
            $property->owner_id = Auth::id();
            $property->is_active = true;
            $property->is_featured = true;

            $this->propertyRepository->save($property);

            // 2️⃣ Collect images and their types dynamically
            $images = [];
            $imageTypes = [];
            $primaryIndex = $request->input('primary_image_index', 0);

            foreach ($request->all() as $key => $value) {
                if (str_starts_with($key, 'images_') && $value instanceof \Illuminate\Http\UploadedFile) {
                    $index = (int) str_replace('images_', '', $key);
                    $images[$index] = $value;
                }
                if (str_starts_with($key, 'image_types_')) {
                    $index = (int) str_replace('image_types_', '', $key);
                    $imageTypes[$index] = $value;
                }
            }

            // 3️⃣ Store images via PropertyImageService
            if (!empty($images)) {
                // Pass associative arrays directly - service will handle sorting
                $imageService->store($property, $images, (int) $primaryIndex, $imageTypes);
            }

            DB::commit();

            return redirect()
                ->route('properties.index')
                ->with('flash', [
                    'type' => 'success',
                    'message' => __('Property successfully created.'),
                ]);

        } catch (\Throwable $exception) {
            DB::rollBack();
            report($exception);

            return back()
                ->withInput()
                ->withErrors(__('Failed to create property: ' . $exception->getMessage()));
        }
    }


    public function show(Property $property)
    {
        $property->load(['propertyImages', 'owner']);

        return Inertia::render('properties/Show', [
            'property' => new PropertyResource($property),
        ]);
    }

    public function edit(Property $property)
    {
        $property->load(['propertyImages', 'owner']);

        dd($property);
        
        return Inertia::render('properties/Edit', [
            'property' => new PropertyResource($property),
            'property_types' => PropertyType::collection(),
            'statuses' => Status::collection(),
            'image_types' => ImageType::collection(),
        ]);
    }

    public function update( UpdateRequest $request,Property $property, PropertyImageService $imageService ) 
    {
        DB::beginTransaction();

        try {
            $data = collect($request->validated())
                ->except([
                    'images',
                    'image_types',
                    'delete_images',
                    'update_images',
                    'primary_image_id',
                ])
                ->toArray();

            $property->forceFill($data);
            $this->propertyRepository->save($property);

            if ($request->filled('delete_images')) {
                $imageService->deleteForProperty(
                    $property,
                    $request->input('delete_images')
                );
            }

            if ($request->hasFile('images')) {
                $imageService->update(
                    $property,
                    $request->file('images'),
                    [], 
                    null,
                    $request->input('image_types', [])
                );
            }

            if ($request->filled('update_images')) {
                foreach ($request->input('update_images') as $image) {

                    if (isset($image['sort_order'])) {
                        $imageService->reorder($property, [
                            $image['id'] => $image['sort_order'],
                        ]);
                    }

                    if (isset($image['image_type'])) {
                        $imageService->updateImageType(
                            $property,
                            $image['id'],
                            $image['image_type']
                        );
                    }

                    if (!empty($image['is_primary'])) {
                        $imageService->setPrimaryImage(
                            $property,
                            $image['id']
                        );
                    }
                }
            }

            if ($request->filled('primary_image_id')) {
                $imageService->setPrimaryImage(
                    $property,
                    $request->input('primary_image_id')
                );
            }

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
