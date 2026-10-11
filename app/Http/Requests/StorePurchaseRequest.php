<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('purchases create');
    }

    public function rules(): array
    {
        $businessId = $this->user()->business_id;

        return [
            'supplier_id' => [
                'required',
                'integer',
                Rule::exists('suppliers', 'id')->where(function ($query) use ($businessId) {
                    $query->where('business_id', $businessId);
                }),
            ],
            'invoice_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('purchases')->where(function ($query) use ($businessId) {
                    return $query->where('business_id', $businessId);
                }),
            ],
            'purchase_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'supplier_id.exists' => 'The selected supplier does not exist or does not belong to your business.',
            'invoice_number.unique' => 'This invoice number already exists for your business.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'business_id' => $this->user()->business_id,
            'user_id' => $this->user()->id,
            'status' => 'draft',
            'total_amount' => 0,
        ]);
    }
}
