<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            // Ignore the current user's email for the unique check
            'email'    => ['required', 'email', 'unique:users,email,' . $this->route('user')->id],
            'password' => ['nullable', 'string', 'min:8'], // Password is optional on update
            'store_id' => [
                'required', 
                Rule::exists('stores', 'id')->where('company_id', auth()->user()->company_id)
            ],
            'role_id'  => [
                'required',
                Rule::exists('roles', 'id')->where(function ($query) {
                    $query->where('company_id', auth()->user()->company_id)
                        ->whereNotIn('slug', ['owner', 'super_admin', 'customer']);
                }),
            ],
            'status'   => ['required', 'in:active,inactive']
        ];
    }
}
