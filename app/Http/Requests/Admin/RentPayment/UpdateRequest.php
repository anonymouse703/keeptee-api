<?php

namespace App\Http\Requests\Admin\RentPayment;

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
            'lease_id'       => 'sometimes|exists:leases,id',
            'amount'         => 'sometimes|numeric|min:0',
            'due_date'       => 'sometimes|date|after_or_equal:today',
            'paid_at'        => 'sometimes|date|after_or_equal:due_date|nullable',
            'status'         => 'sometimes|in:pending,paid,overdue',
            'payment_method' => 'sometimes|string|max:50',
        ];
    }
}
