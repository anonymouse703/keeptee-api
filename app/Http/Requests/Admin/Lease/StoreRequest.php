<?php

namespace App\Http\Requests\Admin\Lease;

use App\Enums\Lease\Status;
use Illuminate\Validation\Rule;
use App\Enums\Lease\DocumentType;
use App\Enums\Lease\LateFeeType;
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
            'tenant_id' => 'required|exists:tenants,id',
            'monthly_rent' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => ['required', 'string', Rule::enum(Status::class)],
            'terms' => 'nullable|string',
            'notes' => 'nullable|string',
            'rent_due_day' => 'required|integer|min:1|max:31',
            'grace_period_days' => 'nullable|integer|min:0',
            'late_fee_type' => ['required', 'string', Rule::enum(LateFeeType::class)],
            'late_fee_value' => 'required|numeric|min:0',
            'late_fee_cap' => 'nullable|numeric|min:0',
            'file_document_types' => 'nullable|array',
            'file_document_types.*' => ['required', 'string', Rule::enum(DocumentType::class)],
            'files' => 'nullable|array',
            'files.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240', // 10MB max
        ];
    }
}