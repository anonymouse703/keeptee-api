<?php

namespace App\Http\Requests\Admin\Tenant;

use Illuminate\Validation\Rule;
use App\Enums\Tenant\DocumentType;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => 'sometimes|required|string|max:255',
            'email'       => 'nullable|email|max:255',
            'phone'       => 'sometimes|required|string|max:20',
            'address'     => 'required|string|max:255',
            
            // File validation
            'files'       => ['nullable', 'array', 'max:10'],
            'files.*'     => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'], 
            
            // Document types
            'file_document_types'    => ['nullable', 'array'], 
            'file_document_types.*'  => ['nullable', 'string', Rule::enum(DocumentType::class)],
            
            // Files to delete
            'delete_files' => ['nullable', 'array'],
            'delete_files.*' => ['nullable', 'string'], // URLs
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tenant name is required.',
            'phone.required' => 'Phone number is required.',
            'lease_end.after_or_equal' => 'Lease end date must be after or equal to start date.',
            'files.*.file' => 'Each upload must be a valid file.',
            'files.*.mimes' => 'Files must be PDF, DOC, DOCX, or image files.',
            'files.*.max' => 'Each file must not exceed 10MB.',
            'file_document_types.*.enum' => 'Invalid document type selected.',
        ];
    }
}