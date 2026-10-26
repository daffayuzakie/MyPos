<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('user'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'contact' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', Rule::in($this->assignableRoles())],
            'branch_id' => [
                'nullable',
                'integer',
                Rule::exists('branches', 'id')->where('business_id', $this->user()->business_id),
            ],
            'pin' => ['nullable', 'string', 'min:4', 'max:6'],
            'password' => ['nullable', 'string', 'min:8'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'role.in' => 'Peran yang dipilih tidak valid.',
            'branch_id.exists' => 'Cabang tidak valid.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Prevent business_id from being changed via request.
        $this->request->remove('business_id');
    }

    /**
     * Roles the current user is allowed to assign.
     *
     * @return array<int, string>
     */
    private function assignableRoles(): array
    {
        return $this->user()->hasRole('admin')
            ? ['admin', 'manajer', 'kasir']
            : ['manajer', 'kasir'];
    }
}
