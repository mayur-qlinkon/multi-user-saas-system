<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            
            // Ensure the store they select actually belongs to their company!
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
