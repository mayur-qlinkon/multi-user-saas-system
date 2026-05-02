<?php

namespace App\Http\Requests\Admin\Hrm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = auth()->user()->company_id;
        // Fetch the employee instance from the route to get the associated user_id
        $employee = $this->route('employee');
        $userId = $employee->user_id ?? null;
        return [
            // User Account Validation
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 
                'string', 
                'email', 
                'max:255', 
                // Ensure email is unique across the company, but ignore this employee's current user account
                Rule::unique('users', 'email')->where('company_id', $companyId)->ignore($userId)
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8'], // Nullable so they can leave it blank
            // Employment Details
            'store_id' => ['required', Rule::exists('stores', 'id')->where('company_id', $companyId)],
            'department_id' => ['nullable', Rule::exists('departments', 'id')->where('company_id', $companyId)],
            'designation_id' => ['nullable', Rule::exists('designations', 'id')->where('company_id', $companyId)],
            'shift_id' => ['nullable', Rule::exists('shifts', 'id')->where('company_id', $companyId)],
            'reporting_to' => ['nullable', Rule::exists('employees', 'id')->where('company_id', $companyId)],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'marital_status' => ['nullable', Rule::in(['single', 'married', 'divorced', 'widowed'])],
            'blood_group' => ['nullable', 'string', 'max:5'],
            'date_of_joining' => ['required', 'date'],
            'date_of_leaving' => ['nullable', 'date', 'after:date_of_joining'],
            'probation_end_date' => ['nullable', 'date', 'after:date_of_joining'],
            'employment_type' => ['required', Rule::in(['full_time', 'part_time', 'contract', 'intern', 'freelancer'])],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'terminated', 'on_notice', 'absconding'])],
            'exit_reason' => ['nullable', 'string'],
            'basic_salary' => ['nullable', 'numeric', 'min:0'],
            'salary_type' => ['nullable', Rule::in(['monthly', 'daily', 'hourly'])],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:30'],
            'bank_ifsc' => ['nullable', 'string', 'max:20'],
            'bank_branch' => ['nullable', 'string', 'max:100'],
            'pan_number' => ['nullable', 'string', 'size:10', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/'],
            'aadhaar_number' => ['nullable', 'string', 'size:12', 'regex:/^[0-9]{12}$/'],
            'uan_number' => ['nullable', 'string', 'max:20'],
            'esi_number' => ['nullable', 'string', 'max:20'],
            'pf_number' => ['nullable', 'string', 'max:30'],
            'current_address' => ['nullable', 'string'],
            'permanent_address' => ['nullable', 'string'],
            'emergency_contact_name' => ['nullable', 'string', 'max:100'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'emergency_contact_relation' => ['nullable', 'string', 'max:50'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'id_proof' => ['nullable', 'file', 'max:5120'],
            'address_proof' => ['nullable', 'file', 'max:5120'],
            'notes' => ['nullable', 'string'],
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'Please provide the employee\'s full name.',
            'email.required' => 'An email address is required for the employee login.',
            'email.unique' => 'This email is already in use by another account in your company. Please use a different email.',
            'password.min' => 'For security, the password must be at least 8 characters long.',
            'store_id.required' => 'Please select a primary branch/store for this employee.',
            'date_of_joining.required' => 'The date of joining is required.',
            'pan_number.regex' => 'The PAN number format seems incorrect. It should be like ABCDE1234F.',
            'aadhaar_number.regex' => 'The Aadhaar number must be exactly 12 digits.',
        ];
    }
}
