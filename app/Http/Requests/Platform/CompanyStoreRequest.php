<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;

class CompanyStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Assuming middleware handles super_admin check
    }

    public function rules(): array
    {
        return [
            // Company Fields
            'company_name' => ['required', 'string', 'max:150'],
            'company_email' => ['required', 'email', 'max:150'],
            'slug' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:companies,slug'],
            'phone' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:100'],
            'state_id' => ['nullable', 'exists:states,id'],
            'gst_number' => ['nullable', 'string', 'max:15'],
            'is_active' => ['boolean'],

            // Owner Fields
            'owner_name' => ['required', 'string', 'max:100'],
            'owner_email' => ['required', 'email', 'unique:users,email'],
            'owner_password' => ['required', 'string', 'min:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => 'Slug may only contain lowercase letters, numbers, and hyphens.',
            'slug.unique' => 'This slug is already taken by another company.',
        ];
    }
}
