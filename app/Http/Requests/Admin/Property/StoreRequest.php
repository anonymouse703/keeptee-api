<?php

namespace App\Http\Requests\Admin\Property;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'status'         => 'required|in:available,sold,rented',
            'property_type'  => 'required|string|max:100',
            'price'          => 'required|numeric|min:0',
            'bedrooms'       => 'required|integer|min:0',
            'bathrooms'      => 'required|integer|min:0',
            'floor_area'     => 'required|numeric|min:0',
            'address'        => 'required|string|max:500',
            'city'           => 'required|string|max:100',
            'state'          => 'nullable|string|max:100',
            'country'        => 'required|string|max:100',
            'latitude'       => 'nullable|numeric|between:-90,90',
            'longitude'      => 'nullable|numeric|between:-180,180',
            'owner_id'       => 'required|exists:users,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }
}
