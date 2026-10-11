<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('purchases edit');
    }

    public function rules(): array
    {
        $businessId = $this->user()->business_id;
        $purchaseId = $this->route('purchase')->id;

        return [
            'supplier_id' => [
                'required',
                'integer',
                'exists:suppliers,id',
                Rule::unique('purchases')->where(function ($query) use ($businessId, $purchaseId) {
                    return $query->where('business_id', $businessId)->where('id', '!=', $purchaseId);
                }),
            ],
            'invoice_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('purchases')->where(function ($query) use ($businessId, $purchaseId) {
                    return $query->where('business_id', $businessId)->where('id', '!=', $purchaseId);
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
}
