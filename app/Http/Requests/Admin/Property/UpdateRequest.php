<?php

namespace App\Http\Requests\Admin\Property;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
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
            'description'    => 'sometimes|string|nullable',
            'status'         => 'sometimes|in:for_sale,for_rent,sold,rented,available',
            'property_type'  => 'sometimes|string|max:100',
            'price'          => 'sometimes|numeric|min:0',
            'bedrooms'       => 'sometimes|integer|min:0',
            'bathrooms'      => 'sometimes|integer|min:0',
            'floor_area'     => 'sometimes|numeric|min:0',
            'address'        => 'sometimes|string|max:500',
            'city'           => 'sometimes|string|max:100',
            'state'          => 'sometimes|string|max:100|nullable',
            'country'        => 'sometimes|string|max:100',
            'latitude'       => 'sometimes|numeric|between:-90,90',
            'longitude'      => 'sometimes|numeric|between:-180,180',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }
}
