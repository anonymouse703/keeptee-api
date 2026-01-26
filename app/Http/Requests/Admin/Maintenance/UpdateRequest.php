<?php

namespace App\Http\Requests\Admin\Maintenance;

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
            'property_id'    => 'sometimes|exists:properties,id',
            'tenant_id'      => 'sometimes|exists:tenants,id',
            'title'          => 'sometimes|string|max:255',
            'description'    => 'sometimes|string|nullable',
            'status'         => 'sometimes|in:pending,confirmed,completed,cancelled',
            'scheduled_date' => 'sometimes|date|after_or_equal:today',
        ];
    }
}
