<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('stock create');
    }

    public function rules(): array
    {
        $businessId = $this->user()->business_id;

        return [
            'product_id' => [
                'required',
                Rule::exists('products', 'id')->where('business_id', $this->user()->business_id)->where('is_active', true),
            ],
            'type' => ['required', Rule::in(['in', 'out', 'adjustment'])],
            'quantity' => ['required', 'integer', function ($attribute, $value, $fail) {
                $type = $this->input('type');
                if ($type === 'adjustment') {
                    if ($this->input('quantity') == 0) {
                        $fail('Adjustment quantity cannot be zero.');
                    }
                } else {
                    if ($this->input('quantity') <= 0) {
                        $fail('Quantity must be positive for IN and OUT.');
                    }
                }
            }],
            'reason' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.exists' => 'The selected product does not belong to your business or is inactive.',
            'quantity.min' => 'Quantity must be positive for IN and OUT.',
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'type' => $this->type,
        ]);
    }
}
