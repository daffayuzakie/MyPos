<?php

namespace App\Http\Requests;

use App\Models\Category;
use App\Models\Unit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('products create');
    }

    protected function prepareForValidation(): void
    {
        $businessId = $this->user()->business_id;

        if (! $this->filled('sku')) {
            $this->merge([
                'sku' => 'SKU-'.strtoupper(Str::random(6)),
            ]);
        }

        if (! $this->filled('purchase_price')) {
            $this->merge([
                'purchase_price' => round((float) ($this->selling_price ?? 0) * 0.7, 2),
            ]);
        }

        if (! $this->filled('unit_id')) {
            $unit = Unit::where('business_id', $businessId)->first();
            if (! $unit) {
                $unit = Unit::create([
                    'business_id' => $businessId,
                    'name' => 'Pcs',
                    'code' => 'pcs',
                    'is_active' => true,
                ]);
            }
            $this->merge(['unit_id' => $unit->id]);
        }

        if (! $this->filled('category_id')) {
            $category = Category::where('business_id', $businessId)->first();
            if (! $category) {
                $category = Category::create([
                    'business_id' => $businessId,
                    'name' => 'Umum',
                    'is_active' => true,
                ]);
            }
            $this->merge(['category_id' => $category->id]);
        }

        if (! $this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }
    }

    public function rules(): array
    {
        $businessId = $this->user()->business_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => [
                'required',
                'string',
                'max:50',
                Rule::unique('products', 'sku')->where('business_id', $businessId),
            ],
            'barcode' => ['nullable', 'string', 'max:50', Rule::unique('products', 'barcode')->where(function ($query) use ($businessId) {
                $query->whereNotNull('barcode')->where('business_id', $businessId);
            })],
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where('business_id', $businessId)->where('is_active', true),
            ],
            'unit_id' => [
                'required',
                Rule::exists('units', 'id')->where('business_id', $businessId)->where('is_active', true),
            ],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
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
