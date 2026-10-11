<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('categories edit');
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')->id ?? 0;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->where('business_id', $this->user()->business_id)->ignore($categoryId)],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Category with this name already exists in your business.',
        ];
    }
}
