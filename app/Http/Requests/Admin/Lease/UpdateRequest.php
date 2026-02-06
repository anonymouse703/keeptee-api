<?php

namespace App\Http\Requests\Admin\Lease;

use App\Enums\Lease\Status;
use Illuminate\Validation\Rule;
use App\Enums\Lease\LateFeeType;
use App\Enums\Lease\DocumentType;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Changed from false to true
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'property_id' => 'sometimes|exists:properties,id',
            'tenant_id' => 'sometimes|exists:tenants,id',
            'monthly_rent' => 'sometimes|numeric|min:0',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'status' => ['required', 'string', Rule::enum(Status::class)],
            'terms' => 'sometimes|string',
            'notes' => 'sometimes|string',
            'rent_due_day' => 'sometimes|integer|min:1|max:31',
            'grace_period_days' => 'sometimes|integer|min:0',
            'late_fee_type' => ['required', 'string', Rule::enum(LateFeeType::class)],
            'late_fee_value' => 'sometimes|numeric|min:0',
            'late_fee_cap' => 'sometimes|numeric|min:0',
            'file_document_types' => 'sometimes|array',
            'file_document_types.*' => ['required', 'string', Rule::enum(DocumentType::class)],
            'files' => 'sometimes|array',
            'files.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ];
    }
}