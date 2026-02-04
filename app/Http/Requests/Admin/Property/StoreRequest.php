<?php

namespace App\Http\Requests\Admin\Property;

use Illuminate\Validation\Rule;
use App\Enums\Property\ImageType;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'property_type' => 'required|string',
            'status' => 'required|string',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'floor_area' => 'nullable|numeric|min:0',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'nullable|string',
            'country' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'image_types' => 'nullable|array|size:' . (isset($this->images) ? count($this->images) : 0),
            'image_types.*' => ['string', Rule::in(ImageType::values())], 
            'primary_image_index' => 'nullable|integer|min:0',
            'delete_images' => 'nullable|array',
            'tags' => 'nullable|array',
            'tags.*' => [
                'integer',
                Rule::exists('tags', 'id'),
            ],
            'amenities' => 'nullable|array',
            'amenities.*' => [
                'integer',
                Rule::exists('amenities', 'id'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'images.*.file' => 'The uploaded file must be a valid file.',
            'images.*.image' => 'The file must be an image (JPEG, PNG, JPG, GIF, or WebP).',
            'images.*.mimes' => 'The image must be a JPEG, PNG, JPG, GIF, or WebP file.',
            'images.*.max' => 'Each image must not exceed 10MB.',
            'image_types.*.in' => 'The image type must be one of: exterior, interior, kitchen, bathroom, bedroom, floor_plan, amenity, or other.',
            'primary_image_index.min' => 'The primary image index must be at least 0.',
        ];
    }
}