<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('sales create');
    }

    public function rules(): array
    {
        $businessId = $this->user()->business_id;

        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where('business_id', $businessId)->where('is_active', true),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'received_cash' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Cart cannot be empty.',
            'items.min' => 'Cart must have at least one item.',
            'items.*.product_id.exists' => 'One or more products are invalid or not available.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'received_cash.required' => 'Received cash amount is required.',
            'received_cash.min' => 'Received cash must be at least 0.',
        ];
    }
}
