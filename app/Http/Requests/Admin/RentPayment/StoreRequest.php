<?php

namespace App\Http\Requests\Admin\RentPayment;

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
            'lease_id'       => 'required|exists:leases,id',
            'amount'         => 'required|numeric|min:0',
            'due_date'       => 'required|date|after_or_equal:today',
            'paid_at'        => 'nullable|date|after_or_equal:due_date',
            'status'         => 'required|in:pending,paid,overdue',
            'payment_method' => 'required|string|max:50',
        ];
    }
}
