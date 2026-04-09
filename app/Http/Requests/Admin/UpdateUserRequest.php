<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && has_permission('users.edit');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $companyId = auth()->user()->company_id;

        // Grab the user ID from the route.
        // Assumes your route looks like: Route::put('/users/{user}', ...)
        $userId = $this->route('user') ? $this->route('user')->id : $this->input('user_id');

        return [
            'name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s\.\-]+$/'],

            'email' => [
                'required',
                'email:rfc,dns',
                'max:150',
                // Ignore the current user's ID during the unique check
                Rule::unique('users', 'email')
                    ->where('company_id', $companyId)
                    ->ignore($userId),
            ],

            'phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[0-9\-\+\s\(\)]+$/',
                Rule::unique('users', 'phone')
                    ->where('company_id', $companyId)
                    ->ignore($userId),
            ],

            // Password is optional on update
            'password' => ['nullable', 'string', 'min:6'],
            // 🌟 ADD THESE TWO LINES:
            'store_ids' => ['nullable', 'array'],
            'store_ids.*' => ['integer', 'exists:stores,id'],

            'role_id' => [
                'nullable',
                Rule::exists('roles', 'id')->where(function ($query) use ($companyId) {
                    $query->where('company_id', $companyId)->orWhereNull('company_id');
                }),
            ],

            'status' => ['required', 'string', Rule::in(['active', 'inactive', 'suspended'])],

            // Profile & Contact
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
            'email.required' => 'An email address is required.',
            'email.unique' => 'Another user is already using this email address.',
            'phone.unique' => 'This phone number is already registered to another user.',
            'password.confirmed' => 'The password confirmation does not match.',
            'role_id.required' => 'Please assign a role to this user.',
            'role_id.exists' => 'The selected role is invalid.',
            'image.max' => 'The profile image must not be larger than 2MB.',
        ];
    }
}
