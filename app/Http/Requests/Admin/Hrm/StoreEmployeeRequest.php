<?php

namespace App\Http\Requests\Admin\Hrm;

use App\Models\User;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = auth()->user()->company_id;

        return [
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where('company_id', $companyId),
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (blank($value)) {
                        return;
                    }

                    $isEligibleUser = User::query()
                        ->internal()
                        ->where('status', 'active')
                        ->whereKey($value)
                        ->exists();

                    if (! $isEligibleUser) {
                        $fail('Please select an active staff user.');
                    }
                },
                Rule::unique('employees')->where('company_id', $companyId),
            ],
            'store_id' => ['required', Rule::exists('stores', 'id')->where('company_id', $companyId)],
            'store_ids' => ['nullable', 'array'],
            'store_ids.*' => [Rule::exists('stores', 'id')->where('company_id', $companyId)],
            'department_id' => ['nullable', Rule::exists('departments', 'id')->where('company_id', $companyId)],
            'designation_id' => ['nullable', Rule::exists('designations', 'id')->where('company_id', $companyId)],
            'shift_id' => ['required', Rule::exists('shifts', 'id')->where('company_id', $companyId)],
            'reporting_to' => ['nullable', Rule::exists('employees', 'id')->where('company_id', $companyId)],
            'employee_code' => ['nullable', 'string', 'max:30', Rule::unique('employees')->where('company_id', $companyId)],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'marital_status' => ['nullable', Rule::in(['single', 'married', 'divorced', 'widowed'])],
            'blood_group' => ['nullable', 'string', 'max:5'],
            'date_of_joining' => ['required', 'date'],
            'date_of_leaving' => ['nullable', 'date', 'after:date_of_joining'],
            'probation_end_date' => ['nullable', 'date', 'after:date_of_joining'],
            'employment_type' => ['required', Rule::in(['full_time', 'part_time', 'contract', 'intern', 'freelancer'])],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'terminated', 'on_notice', 'absconding'])],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'salary_type' => ['required', Rule::in(['monthly', 'daily', 'hourly'])],
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
            'user_id.unique' => 'This user is already registered as an employee.',
            'pan_number.regex' => 'PAN number must be in format ABCDE1234F.',
            'aadhaar_number.regex' => 'Aadhaar number must be 12 digits.',
        ];
    }
}
