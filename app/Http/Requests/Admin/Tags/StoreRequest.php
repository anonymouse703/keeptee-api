<?php

namespace App\Http\Requests\Admin\Tags;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:tags,name',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The tag name is required.',
            'name.unique' => 'A tag with this name already exists.',
            'color.regex' => 'The color must be a valid hex color code.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'color' => $this->color ?? '#3b82f6',
            'is_active' => $this->is_active ?? true,
        ]);
    }
}
