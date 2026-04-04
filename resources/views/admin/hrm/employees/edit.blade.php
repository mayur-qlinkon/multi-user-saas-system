@extends('layouts.admin')

@section('title', 'Edit Employee')

@section('header-title')
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.hrm.employees.show', $employee) }}"
            class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
        </a>
        <div>
            <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Edit Employee</h1>
            <p class="text-xs text-gray-400 font-medium mt-0.5">Update employee record for {{ $employee->user->name }}</p>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .form-section {
        background: #fff;
        border: 1.5px solid #f1f5f9;
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 16px;
    }

    .section-head {
        padding: 13px 18px;
        border-bottom: 1px solid #f8fafc;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-icon {
        width: 28px; height: 28px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    .section-title {
        font-size: 12px;
        font-weight: 800;
        color: #374151;
        letter-spacing: 0.03em;
    }

    .section-body { padding: 18px; }

    .field-label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 5px;
    }

    .field-input {
        width: 100%;
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        padding: 9px 13px;
        font-size: 13px;
        color: #1f2937;
        outline: none;
        font-family: inherit;
        background: #fff;
        transition: border-color 150ms ease, box-shadow 150ms ease;
    }

    .field-input:focus {
        border-color: var(--brand-600);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand-600) 10%, transparent);
    }

    select.field-input {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2.5' stroke-linecap='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 36px;
        cursor: pointer;
    }

    .field-input.has-error { border-color: #f43f5e; }

    .field-error {
        font-size: 11px;
        font-weight: 600;
        color: #f43f5e;
        margin-top: 4px;
    }

    .field-readonly {
        width: 100%;
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        padding: 9px 13px;
        font-size: 13px;
        color: #6b7280;
        background: #f9fafb;
        font-family: inherit;
    }

    .sticky-footer {
        position: sticky;
        bottom: 0;
        background: #fff;
        border-top: 1.5px solid #f1f5f9;
        padding: 14px 24px;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
        z-index: 20;
        border-radius: 0 0 16px 16px;
    }
</style>
@endpush

@section('content')

<div class="pb-10" x-data="{ status: '{{ old('status', $employee->status) }}' }">

    <form method="POST" action="{{ route('admin.hrm.employees.update', $employee) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- ── Validation error banner ── --}}
        @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 rounded-xl px-4 py-3 flex items-start gap-3">
                <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <div>
                    <p class="text-sm font-semibold text-red-700">Please fix the errors below.</p>
                </div>
            </div>
        @endif

        {{-- ══════════════════════════════════
             Section 1 — Basic Information
        ══════════════════════════════════ --}}
        <div class="form-section">
            <div class="section-head">
                <div class="section-icon" style="background: #eff6ff">
                    <i data-lucide="user" style="width:14px;height:14px;color:#3b82f6"></i>
                </div>
                <span class="section-title">Basic Information</span>
            </div>
            <div class="section-body">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-5 gap-y-4">

                    {{-- User (read-only) --}}
                    <div>
                        <label class="field-label">User</label>
                        <div class="field-readonly">{{ $employee->user->name }}</div>
                    </div>

                    {{-- Employee Code (read-only) --}}
                    <div>
                        <label class="field-label">Employee Code</label>
                        <div class="field-readonly">{{ $employee->employee_code }}</div>
                    </div>

                    {{-- Date of Joining --}}
                    <div>
                        <label class="field-label">Date of Joining <span class="text-red-500">*</span></label>
                        <input type="date" name="date_of_joining" value="{{ old('date_of_joining', $employee->date_of_joining?->format('Y-m-d')) }}"
                            class="field-input {{ $errors->has('date_of_joining') ? 'has-error' : '' }}">
                        @error('date_of_joining')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Employment Type --}}
                    <div>
                        <label class="field-label">Employment Type</label>
                        <select name="employment_type" class="field-input {{ $errors->has('employment_type') ? 'has-error' : '' }}">
                            <option value="">Select type</option>
                            @foreach(['full_time' => 'Full Time', 'part_time' => 'Part Time', 'contract' => 'Contract', 'intern' => 'Intern', 'freelancer' => 'Freelancer'] as $val => $label)
                                <option value="{{ $val }}" {{ old('employment_type', $employee->employment_type) == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('employment_type')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Department --}}
                    <div>
                        <label class="field-label">Department</label>
                        <select name="department_id" class="field-input {{ $errors->has('department_id') ? 'has-error' : '' }}">
                            <option value="">Select department</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ old('department_id', $employee->department_id) == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Designation --}}
                    <div>
                        <label class="field-label">Designation</label>
                        <select name="designation_id" class="field-input {{ $errors->has('designation_id') ? 'has-error' : '' }}">
                            <option value="">Select designation</option>
                            @foreach($designations as $designation)
                                <option value="{{ $designation->id }}" {{ old('designation_id', $employee->designation_id) == $designation->id ? 'selected' : '' }}>{{ $designation->name }}</option>
                            @endforeach
                        </select>
                        @error('designation_id')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Store --}}
                    <div>
                        <label class="field-label">Store</label>
                        <select name="store_id" class="field-input {{ $errors->has('store_id') ? 'has-error' : '' }}">
                            <option value="">Select store</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" {{ old('store_id', $employee->store_id) == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                            @endforeach
                        </select>
                        @error('store_id')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Additional Store Access --}}
                    <div>
                        <label class="field-label">Additional Store Access</label>
                        <select name="store_ids[]" multiple size="4" class="field-input {{ $errors->has('store_ids') || $errors->has('store_ids.*') ? 'has-error' : '' }}">
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" {{ in_array($store->id, old('store_ids', $employee->user?->stores->pluck('id')->all() ?? [])) ? 'selected' : '' }}>{{ $store->name }}</option>
                            @endforeach
                        </select>
                        @error('store_ids')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                        @error('store_ids.*')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Shift --}}
                    <div>
                        <label class="field-label">Shift</label>
                        <select name="shift_id" class="field-input {{ $errors->has('shift_id') ? 'has-error' : '' }}">
                            <option value="">Select shift</option>
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}" {{ old('shift_id', $employee->shift_id) == $shift->id ? 'selected' : '' }}>{{ $shift->name }}</option>
                            @endforeach
                        </select>
                        @error('shift_id')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Reporting Manager --}}
                    <div>
                        <label class="field-label">Reporting Manager</label>
                        <select name="reporting_to" class="field-input {{ $errors->has('reporting_to') ? 'has-error' : '' }}">
                            <option value="">Select manager</option>
                            @foreach($managers as $manager)
                                <option value="{{ $manager->id }}" {{ old('reporting_to', $employee->reporting_to) == $manager->id ? 'selected' : '' }}>{{ $manager->employee_code }} - {{ $manager->user->name }}</option>
                            @endforeach
                        </select>
                        @error('reporting_to')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div>
                        <label class="field-label">Status <span class="text-red-500">*</span></label>
                        <select name="status" x-model="status" class="field-input {{ $errors->has('status') ? 'has-error' : '' }}">
                            @foreach(['active' => 'Active', 'inactive' => 'Inactive', 'terminated' => 'Terminated', 'on_notice' => 'On Notice', 'absconding' => 'Absconding'] as $val => $label)
                                <option value="{{ $val }}" {{ old('status', $employee->status) == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                {{-- Exit Reason (shown for terminated / absconding) --}}
                <div x-show="status === 'terminated' || status === 'absconding'" x-cloak class="mt-4">
                    <label class="field-label">Exit Reason</label>
                    <textarea name="exit_reason" rows="3"
                        placeholder="Reason for termination or absconding..."
                        class="field-input resize-none {{ $errors->has('exit_reason') ? 'has-error' : '' }}">{{ old('exit_reason', $employee->exit_reason) }}</textarea>
                    @error('exit_reason')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════
             Section 2 — Personal Details
        ══════════════════════════════════ --}}
        <div class="form-section">
            <div class="section-head">
                <div class="section-icon" style="background: #fdf2f8">
                    <i data-lucide="heart" style="width:14px;height:14px;color:#ec4899"></i>
                </div>
                <span class="section-title">Personal Details</span>
            </div>
            <div class="section-body">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-5 gap-y-4">

                    {{-- Date of Birth --}}
                    <div>
                        <label class="field-label">Date of Birth</label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $employee->date_of_birth?->format('Y-m-d')) }}"
                            class="field-input {{ $errors->has('date_of_birth') ? 'has-error' : '' }}">
                        @error('date_of_birth')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Gender --}}
                    <div>
                        <label class="field-label">Gender</label>
                        <select name="gender" class="field-input {{ $errors->has('gender') ? 'has-error' : '' }}">
                            <option value="">Select gender</option>
                            @foreach(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $val => $label)
                                <option value="{{ $val }}" {{ old('gender', $employee->gender) == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('gender')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Marital Status --}}
                    <div>
                        <label class="field-label">Marital Status</label>
                        <select name="marital_status" class="field-input {{ $errors->has('marital_status') ? 'has-error' : '' }}">
                            <option value="">Select status</option>
                            @foreach(['single' => 'Single', 'married' => 'Married', 'divorced' => 'Divorced', 'widowed' => 'Widowed'] as $val => $label)
                                <option value="{{ $val }}" {{ old('marital_status', $employee->marital_status) == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('marital_status')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Blood Group --}}
                    <div>
                        <label class="field-label">Blood Group</label>
                        <input type="text" name="blood_group" value="{{ old('blood_group', $employee->blood_group) }}"
                            placeholder="e.g. O+" maxlength="5"
                            class="field-input {{ $errors->has('blood_group') ? 'has-error' : '' }}">
                        @error('blood_group')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════
             Section 3 — Salary Information
        ══════════════════════════════════ --}}
        <div class="form-section">
            <div class="section-head">
                <div class="section-icon" style="background: #f0fdf4">
                    <i data-lucide="indian-rupee" style="width:14px;height:14px;color:#16a34a"></i>
                </div>
                <span class="section-title">Salary Information</span>
            </div>
            <div class="section-body">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-5 gap-y-4">

                    {{-- Basic Salary --}}
                    <div>
                        <label class="field-label">Basic Salary</label>
                        <input type="number" name="basic_salary" value="{{ old('basic_salary', $employee->basic_salary) }}"
                            placeholder="0.00" min="0" step="0.01"
                            class="field-input {{ $errors->has('basic_salary') ? 'has-error' : '' }}">
                        @error('basic_salary')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Salary Type --}}
                    <div>
                        <label class="field-label">Salary Type</label>
                        <select name="salary_type" class="field-input {{ $errors->has('salary_type') ? 'has-error' : '' }}">
                            <option value="">Select type</option>
                            @foreach(['monthly' => 'Monthly', 'daily' => 'Daily', 'hourly' => 'Hourly'] as $val => $label)
                                <option value="{{ $val }}" {{ old('salary_type', $employee->salary_type) == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('salary_type')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════
             Section 4 — Bank Details
        ══════════════════════════════════ --}}
        <div class="form-section">
            <div class="section-head">
                <div class="section-icon" style="background: #fffbeb">
                    <i data-lucide="landmark" style="width:14px;height:14px;color:#d97706"></i>
                </div>
                <span class="section-title">Bank Details</span>
            </div>
            <div class="section-body">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-5 gap-y-4">

                    {{-- Bank Name --}}
                    <div>
                        <label class="field-label">Bank Name</label>
                        <input type="text" name="bank_name" value="{{ old('bank_name', $employee->bank_name) }}"
                            placeholder="Bank name"
                            class="field-input {{ $errors->has('bank_name') ? 'has-error' : '' }}">
                        @error('bank_name')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Account Number --}}
                    <div>
                        <label class="field-label">Account Number</label>
                        <input type="text" name="account_number" value="{{ old('account_number', $employee->account_number) }}"
                            placeholder="Account number"
                            class="field-input {{ $errors->has('account_number') ? 'has-error' : '' }}">
                        @error('account_number')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- IFSC Code --}}
                    <div>
                        <label class="field-label">IFSC Code</label>
                        <input type="text" name="ifsc_code" value="{{ old('ifsc_code', $employee->ifsc_code) }}"
                            placeholder="IFSC code"
                            class="field-input {{ $errors->has('ifsc_code') ? 'has-error' : '' }}">
                        @error('ifsc_code')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Branch --}}
                    <div>
                        <label class="field-label">Branch</label>
                        <input type="text" name="bank_branch" value="{{ old('bank_branch', $employee->bank_branch) }}"
                            placeholder="Branch name"
                            class="field-input {{ $errors->has('bank_branch') ? 'has-error' : '' }}">
                        @error('bank_branch')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════
             Section 5 — Statutory Information
        ══════════════════════════════════ --}}
        <div class="form-section">
            <div class="section-head">
                <div class="section-icon" style="background: #faf5ff">
                    <i data-lucide="shield" style="width:14px;height:14px;color:#a855f7"></i>
                </div>
                <span class="section-title">Statutory Information</span>
            </div>
            <div class="section-body">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-5 gap-y-4">

                    {{-- PAN Number --}}
                    <div>
                        <label class="field-label">PAN Number</label>
                        <input type="text" name="pan_number" value="{{ old('pan_number', $employee->pan_number) }}"
                            placeholder="ABCDE1234F" maxlength="10"
                            class="field-input {{ $errors->has('pan_number') ? 'has-error' : '' }}">
                        @error('pan_number')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Aadhaar Number --}}
                    <div>
                        <label class="field-label">Aadhaar Number</label>
                        <input type="text" name="aadhaar_number" value="{{ old('aadhaar_number', $employee->aadhaar_number) }}"
                            placeholder="12-digit Aadhaar" maxlength="12"
                            class="field-input {{ $errors->has('aadhaar_number') ? 'has-error' : '' }}">
                        @error('aadhaar_number')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- UAN Number --}}
                    <div>
                        <label class="field-label">UAN Number</label>
                        <input type="text" name="uan_number" value="{{ old('uan_number', $employee->uan_number) }}"
                            placeholder="Universal Account Number"
                            class="field-input {{ $errors->has('uan_number') ? 'has-error' : '' }}">
                        @error('uan_number')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- ESI Number --}}
                    <div>
                        <label class="field-label">ESI Number</label>
                        <input type="text" name="esi_number" value="{{ old('esi_number', $employee->esi_number) }}"
                            placeholder="ESI number"
                            class="field-input {{ $errors->has('esi_number') ? 'has-error' : '' }}">
                        @error('esi_number')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- PF Number --}}
                    <div>
                        <label class="field-label">PF Number</label>
                        <input type="text" name="pf_number" value="{{ old('pf_number', $employee->pf_number) }}"
                            placeholder="PF number"
                            class="field-input {{ $errors->has('pf_number') ? 'has-error' : '' }}">
                        @error('pf_number')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════
             Section 6 — Address
        ══════════════════════════════════ --}}
        <div class="form-section">
            <div class="section-head">
                <div class="section-icon" style="background: #f0fdfa">
                    <i data-lucide="map-pin" style="width:14px;height:14px;color:#14b8a6"></i>
                </div>
                <span class="section-title">Address</span>
            </div>
            <div class="section-body">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-4">

                    {{-- Current Address --}}
                    <div>
                        <label class="field-label">Current Address</label>
                        <textarea name="current_address" rows="3"
                            placeholder="Current residential address"
                            class="field-input resize-none {{ $errors->has('current_address') ? 'has-error' : '' }}">{{ old('current_address', $employee->current_address) }}</textarea>
                        @error('current_address')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Permanent Address --}}
                    <div>
                        <label class="field-label">Permanent Address</label>
                        <textarea name="permanent_address" rows="3"
                            placeholder="Permanent residential address"
                            class="field-input resize-none {{ $errors->has('permanent_address') ? 'has-error' : '' }}">{{ old('permanent_address', $employee->permanent_address) }}</textarea>
                        @error('permanent_address')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════
             Section 7 — Emergency Contact
        ══════════════════════════════════ --}}
        <div class="form-section">
            <div class="section-head">
                <div class="section-icon" style="background: #fef2f2">
                    <i data-lucide="phone" style="width:14px;height:14px;color:#ef4444"></i>
                </div>
                <span class="section-title">Emergency Contact</span>
            </div>
            <div class="section-body">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-5 gap-y-4">

                    {{-- Name --}}
                    <div>
                        <label class="field-label">Name</label>
                        <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $employee->emergency_contact_name) }}"
                            placeholder="Contact person name"
                            class="field-input {{ $errors->has('emergency_contact_name') ? 'has-error' : '' }}">
                        @error('emergency_contact_name')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Phone --}}
                    <div>
                        <label class="field-label">Phone</label>
                        <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $employee->emergency_contact_phone) }}"
                            placeholder="Contact phone number"
                            class="field-input {{ $errors->has('emergency_contact_phone') ? 'has-error' : '' }}">
                        @error('emergency_contact_phone')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Relation --}}
                    <div>
                        <label class="field-label">Relation</label>
                        <input type="text" name="emergency_contact_relation" value="{{ old('emergency_contact_relation', $employee->emergency_contact_relation) }}"
                            placeholder="e.g. Spouse, Parent, Sibling"
                            class="field-input {{ $errors->has('emergency_contact_relation') ? 'has-error' : '' }}">
                        @error('emergency_contact_relation')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════
             Section 8 — Notes
        ══════════════════════════════════ --}}
        <div class="form-section">
            <div class="section-head">
                <div class="section-icon" style="background: #f9fafb">
                    <i data-lucide="file-text" style="width:14px;height:14px;color:#6b7280"></i>
                </div>
                <span class="section-title">Notes</span>
            </div>
            <div class="section-body">
                <textarea name="notes" rows="3"
                    placeholder="Any additional notes about this employee..."
                    class="field-input resize-none w-full {{ $errors->has('notes') ? 'has-error' : '' }}">{{ old('notes', $employee->notes) }}</textarea>
                @error('notes')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- ══════════════════════════════════
             Section 9 — Documents & Identity
        ══════════════════════════════════ --}}
        <div class="form-section">
            <div class="section-head">
                <div class="section-icon" style="background: #f3e8ff">
                    <i data-lucide="file-badge" style="width:14px;height:14px;color:#9333ea"></i>
                </div>
                <span class="section-title">Documents & Identity</span>
            </div>
            <div class="section-body">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-x-5 gap-y-4">

                    {{-- Profile Photo --}}
                    <div>
                        <label class="field-label">Profile Photo</label>
                        @if($employee->photo)
                            <div class="mb-2 flex items-center gap-3">
                                <img src="{{ Storage::url($employee->photo) }}" alt="Photo" class="w-10 h-10 rounded-full object-cover border border-gray-200">
                                <span class="text-[10px] text-gray-500 font-medium">Upload new to replace</span>
                            </div>
                        @endif
                        <input type="file" name="photo" accept="image/*" class="field-input {{ $errors->has('photo') ? 'has-error' : '' }}">
                        @error('photo') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    {{-- ID Proof --}}
                    <div>
                        <label class="field-label">ID Proof (PAN/Aadhaar)</label>
                        @if($employee->id_proof)
                            <div class="mb-2">
                                <a href="{{ Storage::url($employee->id_proof) }}" target="_blank" class="text-xs font-bold text-blue-600 hover:underline flex items-center gap-1">
                                    <i data-lucide="external-link" class="w-3 h-3"></i> View Current Document
                                </a>
                            </div>
                        @endif
                        <input type="file" name="id_proof" accept=".pdf,image/*" class="field-input {{ $errors->has('id_proof') ? 'has-error' : '' }}">
                        @error('id_proof') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    {{-- Address Proof --}}
                    <div>
                        <label class="field-label">Address Proof</label>
                        @if($employee->address_proof)
                            <div class="mb-2">
                                <a href="{{ Storage::url($employee->address_proof) }}" target="_blank" class="text-xs font-bold text-blue-600 hover:underline flex items-center gap-1">
                                    <i data-lucide="external-link" class="w-3 h-3"></i> View Current Document
                                </a>
                            </div>
                        @endif
                        <input type="file" name="address_proof" accept=".pdf,image/*" class="field-input {{ $errors->has('address_proof') ? 'has-error' : '' }}">
                        @error('address_proof') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════
             Sticky Footer — Cancel + Update
        ══════════════════════════════════ --}}
        <div class="sticky-footer">
            <a href="{{ route('admin.hrm.employees.show', $employee) }}"
                class="flex items-center justify-center px-5 py-2.5 rounded-xl text-[13px] font-bold text-gray-600 border border-gray-200 hover:bg-gray-50 transition-colors">
                Cancel
            </a>
            <button type="submit"
                class="flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-[14px] font-bold text-white transition-opacity hover:opacity-90"
                style="background: var(--brand-600)">
                <i data-lucide="check" style="width:16px;height:16px"></i>
                Update Employee
            </button>
        </div>

    </form>

</div>
@endsection
