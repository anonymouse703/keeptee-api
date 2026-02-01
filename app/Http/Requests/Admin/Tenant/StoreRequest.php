<?php

namespace App\Http\Requests\Admin\Tenant;

use Illuminate\Validation\Rule;
use App\Enums\Tenant\DocumentType;
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
            'email'       => 'nullable|email|max:255',
            'phone'       => 'required|string|max:20',
            'lease_start' => 'required|date',
            'lease_end'   => 'required|date|after_or_equal:lease_start',
            'file'        => ['nullable', 'array'],
            'file.*'      => ['file', 'mimes:pdf,doc,docx', 'max:10240'], 
            'document_type'    => ['nullable', 'array'], 
            'document_type.*'  => ['nullable', Rule::enum(DocumentType::class)],
        ];
    }
}
