<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && has_permission('users.create');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $companyId = auth()->user()->company_id;

        return [
            'name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s\.\-]+$/'],

            'email' => [
                'required',
                'email:rfc,dns',
                'max:150',
                // Tenant-aware unique check
                Rule::unique('users', 'email')->where('company_id', $companyId),
            ],

            'phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[0-9\-\+\s\(\)]+$/',
                // Tenant-aware unique check
                Rule::unique('users', 'phone')->where('company_id', $companyId),
            ],

            'password' => ['required', 'string', 'min:6'],
            // 🌟 ADD THESE TWO LINES:
            'store_ids' => ['nullable', 'array'],
            'store_ids.*' => ['integer', 'exists:stores,id'],

            'role_id' => [
                'required',
                // Ensure the assigned role actually belongs to this tenant or is a global/system role
                Rule::exists('roles', 'id')->where(function ($query) use ($companyId) {
                    $query->where('company_id', $companyId)->orWhereNull('company_id');
                }),
            ],

            'status' => ['required', 'string', Rule::in(['active', 'inactive', 'suspended'])],

            // Profile & Contact (Optional Fields)
            'phone_number' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'country' => ['nullable', 'string', 'max:100'],
            'state_id' => ['nullable', 'integer', 'exists:states,id'],
            'zip_code' => ['nullable', 'string', 'max:20'],

            // Image Upload
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ];
    }

    /**
     * Custom user-friendly error messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please enter the user\'s full name.',
            'name.regex' => 'The name can only contain letters, spaces, and hyphens.',
            'email.required' => 'An email address is required for login.',
            'email.unique' => 'A user with this email already exists in your company.',
            'phone.unique' => 'This phone number is already registered to another user.',
            'password.required' => 'A secure password is required.',
            'password.confirmed' => 'The password confirmation does not match.',
            'role_id.required' => 'Please assign a role to this user.',
            'role_id.exists' => 'The selected role is invalid or unavailable.',
            'image.max' => 'The profile image must not be larger than 2MB.',
            'image.mimes' => 'Please upload a valid image file (JPG, PNG, or WEBP).',
        ];
    }
}
