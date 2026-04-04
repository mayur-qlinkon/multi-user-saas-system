<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'email' => strtolower($this->email),
        ]);
    }

    public function rules(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Company
            |--------------------------------------------------------------------------
            */

            'company_name' => ['required','string','max:150'],

            /*
            |--------------------------------------------------------------------------
            | User Core
            |--------------------------------------------------------------------------
            */

            'name'     => ['required','string','min:2','max:100'],
            'email'    => ['required','email','max:150','unique:users,email'],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed'
            ],

            /*
            |--------------------------------------------------------------------------
            | Optional Profile
            |--------------------------------------------------------------------------
            */

            'phone_number' => ['nullable','digits_between:10,15'],

            'address'  => ['nullable','string'],
            'state_id' => ['nullable', 'exists:states,id'],
            'country'  => ['nullable','string','max:100'],
            'zip_code' => ['nullable','string','max:20'],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'company_name.required' => 'Company name is required.',
            'name.required'         => 'Full name is required.',
            'email.unique'          => 'This email is already registered.',
            'password.confirmed'    => 'Passwords do not match.',
            'password.min'          => 'Password must be at least 8 characters.',
            'image.max'             => 'Profile image must be under 2MB.',
        ];
    }
}
