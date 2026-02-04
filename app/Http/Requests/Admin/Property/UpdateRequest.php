<?php

namespace App\Http\Requests\Admin\Property;

use Illuminate\Validation\Rule;
use App\Enums\Property\ImageType;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'          => 'sometimes|string|max:255',
            'description'    => 'nullable|string',
            'status'         => 'sometimes|in:for_sale,for_rent,sold,rented,available',
            'property_type'  => 'sometimes|string|max:100',
            'price'          => 'sometimes|numeric|min:0',
            'bedrooms'       => 'sometimes|integer|min:0',
            'bathrooms'      => 'sometimes|integer|min:0',
            'floor_area'     => 'sometimes|numeric|min:0',
            'address'        => 'sometimes|string|max:500',
            'city'           => 'sometimes|string|max:100',
            'state'          => 'nullable|string|max:100',
            'country'        => 'sometimes|string|max:100',
            'latitude'       => 'nullable|numeric|between:-90,90',
            'longitude'      => 'nullable|numeric|between:-180,180',
            'images'         => 'nullable|array|max:10',
            'images.*'       => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'image_types'    => 'nullable|array',
            'image_types.*'  => ['string', Rule::in(ImageType::values())], 
            'delete_images'  => 'nullable|array',
            'delete_images.*' => 'integer|exists:files,id',
            'update_images'         => 'nullable|array',
            'update_images.*.id'    => 'required|integer|exists:files,id',
            'update_images.*.is_primary' => 'nullable|boolean',
            'update_images.*.sort_order' => 'nullable|integer|min:0',
            'update_images.*.image_type' => ['string', Rule::in(ImageType::values())], 
            'primary_image_id' => 'nullable|integer|exists:files,id',
            'tags' => 'sometimes|array',
            'tags.*' => [
                'integer',
                Rule::exists('tags', 'id'),
            ],
            'amenities' => 'sometimes|array',
            'amenities.*' => [
                'integer',
                Rule::exists('amenities', 'id'),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Images must be in JPG, JPEG, PNG, or WEBP format.',
            'images.*.max' => 'Each image must not exceed 2MB.',
            'delete_images.*.exists' => 'One or more images to delete do not exist.',
        ];
    }
}