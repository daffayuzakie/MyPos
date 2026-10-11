<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('purchases create');
    }

    public function rules(): array
    {
        $businessId = $this->user()->business_id;
        $purchaseId = $this->route('purchase')->id;

        return [
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
                Rule::unique('purchase_items')->where(function ($query) use ($purchaseId) {
                    return $query->where('purchase_id', $purchaseId);
                }),
            ],
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0|max:99999999.99',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.exists' => 'The selected product does not exist or does not belong to your business.',
            'product_id.unique' => 'This product is already added to the purchase.',
            'quantity.min' => 'Quantity must be at least 1.',
            'unit_price.min' => 'Unit price cannot be negative.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'subtotal' => $this->input('quantity') * $this->input('unit_price'),
        ]);
    }
}
