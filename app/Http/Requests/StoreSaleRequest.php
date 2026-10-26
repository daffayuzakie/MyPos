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

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $businessId = $this->user()->business_id;

        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')
                    ->where('business_id', $businessId)
                    ->where('is_active', true),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'payment_method' => ['nullable', Rule::in(['cash', 'midtrans'])],
            'received_cash' => ['required_if:payment_method,cash', 'nullable', 'numeric', 'min:0'],
            'discount_type' => ['nullable', Rule::in(['percentage', 'fixed'])],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'voucher_code' => ['nullable', 'string', 'max:100'],
            'customer_id' => [
                'nullable',
                'integer',
                Rule::exists('customers', 'id')->where('business_id', $businessId),
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.required' => 'Keranjang tidak boleh kosong.',
            'items.min' => 'Keranjang harus berisi minimal satu item.',
            'items.*.product_id.exists' => 'Produk tidak valid atau tidak tersedia untuk usaha ini.',
            'items.*.quantity.min' => 'Jumlah minimal 1.',
            'received_cash.required' => 'Jumlah uang diterima wajib diisi.',
            'received_cash.min' => 'Jumlah uang diterima tidak boleh negatif.',
            'customer_id.exists' => 'Pelanggan tidak valid.',
            'discount_value.min' => 'Diskon tidak boleh negatif.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('payment_method')) {
            $this->merge(['payment_method' => 'cash']);
        }
    }
}
