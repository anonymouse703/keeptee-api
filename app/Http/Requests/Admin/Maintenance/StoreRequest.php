<?php

namespace App\Http\Requests\Admin\Maintenance;

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
            'property_id'    => 'required|exists:properties,id',
            'tenant_id'      => 'required|exists:tenants,id',
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'status'         => 'required|in:pending,confirmed,completed,cancelled',
            'scheduled_date' => 'required|date|after_or_equal:today',
        ];
    }
}
