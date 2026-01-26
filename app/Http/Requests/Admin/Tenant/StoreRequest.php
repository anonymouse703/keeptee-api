<?php

namespace App\Http\Requests\Admin\Tenant;

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
            'property_id' => 'required|exists:properties,id',
            'name'        => 'required|string|max:255',
            'phone'       => 'required|string|max:20',
            'lease_start' => 'required|date|after_or_equal:today',
            'lease_end'   => 'required|date|after_or_equal:lease_start',
        ];
    }
}
