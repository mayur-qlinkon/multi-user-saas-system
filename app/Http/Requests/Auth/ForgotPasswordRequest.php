<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:users,email'],
            //  'exists' prevents leaking whether email is registered
            //  (you can remove this and handle silently in service if preferred)
        ];
    }

    public function messages(): array
    {
        return [
            'email.exists' => 'No account found with this email.',
        ];
    }
}