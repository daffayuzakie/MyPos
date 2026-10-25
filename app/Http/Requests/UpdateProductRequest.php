<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('products edit');
    }

    public function rules(): array
    {
        $businessId = $this->user()->business_id;
        $productId = $this->route('product')->id ?? 0;

        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => [
                'required',
                'string',
                'max:50',
                Rule::unique('products', 'sku')->where('business_id', $businessId)->ignore($productId),
            ],
            'barcode' => ['nullable', 'string', 'max:50', Rule::unique('products', 'barcode')->where(function ($query) use ($businessId, $productId) {
                $query->whereNotNull('barcode')->where('business_id', $businessId);
                if ($productId) {
                    $query->where('id', '!=', $productId);
                }
            })],
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where('business_id', $businessId)->where('is_active', true),
            ],
            'unit_id' => [
                'nullable',
                Rule::exists('units', 'id')->where('business_id', $businessId)->where('is_active', true),
            ],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'sku.unique' => 'Product with this SKU already exists in your business.',
            'barcode.unique' => 'Product with this barcode already exists in your business.',
            'category_id.exists' => 'The selected category does not belong to your business or is inactive.',
            'unit_id.exists' => 'The selected unit does not belong to your business or is inactive.',
        ];
    }
}
