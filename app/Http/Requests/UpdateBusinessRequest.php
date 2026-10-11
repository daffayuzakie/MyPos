<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBusinessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('business settings');
    }

    public function rules(): array
    {
        $businessId = $this->user()->business_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('businesses', 'slug')->ignore($businessId)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('businesses', 'email')->ignore($businessId)],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'tax_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'invoice_prefix' => ['nullable', 'string', 'max:10'],
            'invoice_sequence' => ['nullable', 'integer', 'min:1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('slug') || empty($this->slug)) {
            $this->merge([
                'slug' => auth()->user()->business->slug,
            ]);
        }
        if (! $this->has('email') || empty($this->email)) {
            $this->merge([
                'email' => auth()->user()->business->email ?? auth()->user()->email,
            ]);
        }
    }
}
