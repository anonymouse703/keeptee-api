<?php

namespace App\Http\Requests\Admin\Tag;

use Illuminate\Validation\Rule;
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
              'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tags', 'name')->ignore($this->route('tag'))
            ],
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
}
