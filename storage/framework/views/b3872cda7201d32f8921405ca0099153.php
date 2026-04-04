<?php $__env->startSection('title', 'Add New Employee'); ?>

<?php $__env->startSection('header-title'); ?>
    <div class="flex items-center gap-3">
        <a href="<?php echo e(route('admin.hrm.employees.index')); ?>"
            class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
        </a>
        <div>
            <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Add New Employee</h1>
            <p class="text-xs text-gray-400 font-medium mt-0.5">Create a new employee record</p>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
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
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<div class="pb-10">

    <form method="POST" action="<?php echo e(route('admin.hrm.employees.store')); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>

        
        <?php if($errors->any()): ?>
            <div class="mb-4 bg-red-50 border border-red-200 rounded-xl px-4 py-3 flex items-start gap-3">
                <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <div>
                    <p class="text-sm font-semibold text-red-700">Please fix the errors below.</p>
                </div>
            </div>
        <?php endif; ?>

        
        <div class="form-section">
            <div class="section-head">
                <div class="section-icon" style="background: #eff6ff">
                    <i data-lucide="user" style="width:14px;height:14px;color:#3b82f6"></i>
                </div>
                <span class="section-title">Basic Information</span>
            </div>
            <div class="section-body">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-5 gap-y-4">

                    
                    <div>
                        <label class="field-label">User <span class="text-red-500">*</span></label>
                        <select name="user_id" class="field-input <?php echo e($errors->has('user_id') ? 'has-error' : ''); ?>">
                            <option value="">Select user</option>
                            <?php $__currentLoopData = $availableUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($user->id); ?>" <?php echo e(old('user_id') == $user->id ? 'selected' : ''); ?>><?php echo e($user->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['user_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label class="field-label">Employee Code</label>
                        <input type="text" name="employee_code" value="<?php echo e(old('employee_code')); ?>"
                            placeholder="Auto-generated if left empty"
                            class="field-input <?php echo e($errors->has('employee_code') ? 'has-error' : ''); ?>">
                        <?php $__errorArgs = ['employee_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label class="field-label">Date of Joining <span class="text-red-500">*</span></label>
                        <input type="date" name="date_of_joining" value="<?php echo e(old('date_of_joining')); ?>"
                            class="field-input <?php echo e($errors->has('date_of_joining') ? 'has-error' : ''); ?>">
                        <?php $__errorArgs = ['date_of_joining'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label class="field-label">Employment Type</label>
                        <select name="employment_type" class="field-input <?php echo e($errors->has('employment_type') ? 'has-error' : ''); ?>">
                            <option value="">Select type</option>
                            <?php $__currentLoopData = ['full_time' => 'Full Time', 'part_time' => 'Part Time', 'contract' => 'Contract', 'intern' => 'Intern', 'freelancer' => 'Freelancer']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($val); ?>" <?php echo e(old('employment_type') == $val ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['employment_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label class="field-label">Department</label>
                        <select name="department_id" class="field-input <?php echo e($errors->has('department_id') ? 'has-error' : ''); ?>">
                            <option value="">Select department</option>
                            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($department->id); ?>" <?php echo e(old('department_id') == $department->id ? 'selected' : ''); ?>><?php echo e($department->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['department_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label class="field-label">Designation</label>
                        <select name="designation_id" class="field-input <?php echo e($errors->has('designation_id') ? 'has-error' : ''); ?>">
                            <option value="">Select designation</option>
                            <?php $__currentLoopData = $designations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $designation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($designation->id); ?>" <?php echo e(old('designation_id') == $designation->id ? 'selected' : ''); ?>><?php echo e($designation->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['designation_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label class="field-label">Store</label>
                        <select name="store_id" class="field-input <?php echo e($errors->has('store_id') ? 'has-error' : ''); ?>">
                            <option value="">Select store</option>
                            <?php $__currentLoopData = $stores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $store): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($store->id); ?>" <?php echo e(old('store_id') == $store->id ? 'selected' : ''); ?>><?php echo e($store->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['store_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label class="field-label">Reporting Manager</label>
                        <select name="reporting_manager_id" class="field-input <?php echo e($errors->has('reporting_manager_id') ? 'has-error' : ''); ?>">
                            <option value="">Select manager</option>
                            <?php $__currentLoopData = $managers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $manager): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($manager->id); ?>" <?php echo e(old('reporting_manager_id') == $manager->id ? 'selected' : ''); ?>><?php echo e($manager->employee_code); ?> - <?php echo e($manager->user->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['reporting_manager_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                </div>
            </div>
        </div>

        
        <div class="form-section">
            <div class="section-head">
                <div class="section-icon" style="background: #fdf2f8">
                    <i data-lucide="heart" style="width:14px;height:14px;color:#ec4899"></i>
                </div>
                <span class="section-title">Personal Details</span>
            </div>
            <div class="section-body">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-5 gap-y-4">

                    
                    <div>
                        <label class="field-label">Date of Birth</label>
                        
                        <input type="date" name="date_of_birth" 
                            value="<?php echo e(old('date_of_birth', '1999-01-01')); ?>"
                            class="field-input <?php echo e($errors->has('date_of_birth') ? 'has-error' : ''); ?>">
                        <?php $__errorArgs = ['date_of_birth'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label class="field-label">Gender</label>
                        <select name="gender" class="field-input <?php echo e($errors->has('gender') ? 'has-error' : ''); ?>">
                            <option value="">Select gender</option>
                            <?php $__currentLoopData = ['male' => 'Male', 'female' => 'Female', 'other' => 'Other']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($val); ?>" <?php echo e(old('gender') == $val ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['gender'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label class="field-label">Marital Status</label>
                        <select name="marital_status" class="field-input <?php echo e($errors->has('marital_status') ? 'has-error' : ''); ?>">
                            <option value="">Select status</option>
                            <?php $__currentLoopData = ['single' => 'Single', 'married' => 'Married', 'divorced' => 'Divorced', 'widowed' => 'Widowed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($val); ?>" <?php echo e(old('marital_status') == $val ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['marital_status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label class="field-label">Blood Group</label>
                        <input type="text" name="blood_group" value="<?php echo e(old('blood_group')); ?>"
                            placeholder="e.g. O+" maxlength="5"
                            class="field-input <?php echo e($errors->has('blood_group') ? 'has-error' : ''); ?>">
                        <?php $__errorArgs = ['blood_group'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                </div>
            </div>
        </div>

        
        <div class="form-section">
            <div class="section-head">
                <div class="section-icon" style="background: #f0fdf4">
                    <i data-lucide="indian-rupee" style="width:14px;height:14px;color:#16a34a"></i>
                </div>
                <span class="section-title">Salary Information</span>
            </div>
            <div class="section-body">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-5 gap-y-4">

                    
                    <div>
                        <label class="field-label">Basic Salary</label>
                        <input type="number" name="basic_salary" value="<?php echo e(old('basic_salary')); ?>"
                            placeholder="0.00" min="0" step="0.01"
                            class="field-input <?php echo e($errors->has('basic_salary') ? 'has-error' : ''); ?>">
                        <?php $__errorArgs = ['basic_salary'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label class="field-label">Salary Type</label>
                        <select name="salary_type" class="field-input <?php echo e($errors->has('salary_type') ? 'has-error' : ''); ?>">
                            <option value="">Select type</option>
                            <?php $__currentLoopData = ['monthly' => 'Monthly', 'daily' => 'Daily', 'hourly' => 'Hourly']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($val); ?>" <?php echo e(old('salary_type') == $val ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['salary_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                </div>
            </div>
        </div>

        
        <div class="form-section">
            <div class="section-head">
                <div class="section-icon" style="background: #fffbeb">
                    <i data-lucide="landmark" style="width:14px;height:14px;color:#d97706"></i>
                </div>
                <span class="section-title">Bank Details</span>
            </div>
            <div class="section-body">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-5 gap-y-4">

                    
                    <div>
                        <label class="field-label">Bank Name</label>
                        <input type="text" name="bank_name" value="<?php echo e(old('bank_name')); ?>"
                            placeholder="Bank name"
                            class="field-input <?php echo e($errors->has('bank_name') ? 'has-error' : ''); ?>">
                        <?php $__errorArgs = ['bank_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label class="field-label">Account Number</label>
                        <input type="text" name="account_number" value="<?php echo e(old('account_number')); ?>"
                            placeholder="Account number"
                            class="field-input <?php echo e($errors->has('account_number') ? 'has-error' : ''); ?>">
                        <?php $__errorArgs = ['account_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label class="field-label">IFSC Code</label>
                        <input type="text" name="ifsc_code" value="<?php echo e(old('ifsc_code')); ?>"
                            placeholder="IFSC code"
                            class="field-input <?php echo e($errors->has('ifsc_code') ? 'has-error' : ''); ?>">
                        <?php $__errorArgs = ['ifsc_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label class="field-label">Branch</label>
                        <input type="text" name="bank_branch" value="<?php echo e(old('bank_branch')); ?>"
                            placeholder="Branch name"
                            class="field-input <?php echo e($errors->has('bank_branch') ? 'has-error' : ''); ?>">
                        <?php $__errorArgs = ['bank_branch'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                </div>
            </div>
        </div>

        
        <div class="form-section">
            <div class="section-head">
                <div class="section-icon" style="background: #faf5ff">
                    <i data-lucide="shield" style="width:14px;height:14px;color:#a855f7"></i>
                </div>
                <span class="section-title">Statutory Information</span>
            </div>
            <div class="section-body">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-5 gap-y-4">

                    
                    <div>
                        <label class="field-label">PAN Number</label>
                        <input type="text" name="pan_number" value="<?php echo e(old('pan_number')); ?>"
                            placeholder="ABCDE1234F" maxlength="10"
                            class="field-input <?php echo e($errors->has('pan_number') ? 'has-error' : ''); ?>">
                        <?php $__errorArgs = ['pan_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label class="field-label">Aadhaar Number</label>
                        <input type="text" name="aadhaar_number" value="<?php echo e(old('aadhaar_number')); ?>"
                            placeholder="12-digit Aadhaar" maxlength="12"
                            class="field-input <?php echo e($errors->has('aadhaar_number') ? 'has-error' : ''); ?>">
                        <?php $__errorArgs = ['aadhaar_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label class="field-label">UAN Number</label>
                        <input type="text" name="uan_number" value="<?php echo e(old('uan_number')); ?>"
                            placeholder="Universal Account Number"
                            class="field-input <?php echo e($errors->has('uan_number') ? 'has-error' : ''); ?>">
                        <?php $__errorArgs = ['uan_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label class="field-label">ESI Number</label>
                        <input type="text" name="esi_number" value="<?php echo e(old('esi_number')); ?>"
                            placeholder="ESI number"
                            class="field-input <?php echo e($errors->has('esi_number') ? 'has-error' : ''); ?>">
                        <?php $__errorArgs = ['esi_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label class="field-label">PF Number</label>
                        <input type="text" name="pf_number" value="<?php echo e(old('pf_number')); ?>"
                            placeholder="PF number"
                            class="field-input <?php echo e($errors->has('pf_number') ? 'has-error' : ''); ?>">
                        <?php $__errorArgs = ['pf_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                </div>
            </div>
        </div>

        
        <div class="form-section">
            <div class="section-head">
                <div class="section-icon" style="background: #f0fdfa">
                    <i data-lucide="map-pin" style="width:14px;height:14px;color:#14b8a6"></i>
                </div>
                <span class="section-title">Address</span>
            </div>
            <div class="section-body">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-4">

                    
                    <div>
                        <label class="field-label">Current Address</label>
                        <textarea name="current_address" rows="3"
                            placeholder="Current residential address"
                            class="field-input resize-none <?php echo e($errors->has('current_address') ? 'has-error' : ''); ?>"><?php echo e(old('current_address')); ?></textarea>
                        <?php $__errorArgs = ['current_address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label class="field-label">Permanent Address</label>
                        <textarea name="permanent_address" rows="3"
                            placeholder="Permanent residential address"
                            class="field-input resize-none <?php echo e($errors->has('permanent_address') ? 'has-error' : ''); ?>"><?php echo e(old('permanent_address')); ?></textarea>
                        <?php $__errorArgs = ['permanent_address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                </div>
            </div>
        </div>

        
        <div class="form-section">
            <div class="section-head">
                <div class="section-icon" style="background: #fef2f2">
                    <i data-lucide="phone" style="width:14px;height:14px;color:#ef4444"></i>
                </div>
                <span class="section-title">Emergency Contact</span>
            </div>
            <div class="section-body">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-5 gap-y-4">

                    
                    <div>
                        <label class="field-label">Name</label>
                        <input type="text" name="emergency_contact_name" value="<?php echo e(old('emergency_contact_name')); ?>"
                            placeholder="Contact person name"
                            class="field-input <?php echo e($errors->has('emergency_contact_name') ? 'has-error' : ''); ?>">
                        <?php $__errorArgs = ['emergency_contact_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label class="field-label">Phone</label>
                        <input type="text" name="emergency_contact_phone" value="<?php echo e(old('emergency_contact_phone')); ?>"
                            placeholder="Contact phone number"
                            class="field-input <?php echo e($errors->has('emergency_contact_phone') ? 'has-error' : ''); ?>">
                        <?php $__errorArgs = ['emergency_contact_phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label class="field-label">Relation</label>
                        <input type="text" name="emergency_contact_relation" value="<?php echo e(old('emergency_contact_relation')); ?>"
                            placeholder="e.g. Spouse, Parent, Sibling"
                            class="field-input <?php echo e($errors->has('emergency_contact_relation') ? 'has-error' : ''); ?>">
                        <?php $__errorArgs = ['emergency_contact_relation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                </div>
            </div>
        </div>

        
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
                    class="field-input resize-none w-full <?php echo e($errors->has('notes') ? 'has-error' : ''); ?>"><?php echo e(old('notes')); ?></textarea>
                <?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="field-error"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>

        
        <div class="form-section">
            <div class="section-head">
                <div class="section-icon" style="background: #f3e8ff">
                    <i data-lucide="file-badge" style="width:14px;height:14px;color:#9333ea"></i>
                </div>
                <span class="section-title">Documents & Identity</span>
            </div>
            <div class="section-body">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-x-5 gap-y-4">

                    
                    <div>
                        <label class="field-label">Profile Photo (Optional)</label>
                        <input type="file" name="photo" accept="image/*"
                            class="field-input <?php echo e($errors->has('photo') ? 'has-error' : ''); ?>">
                        <p class="text-[10px] text-gray-400 mt-1">JPG, PNG up to 2MB. Will be cropped square.</p>
                        <?php $__errorArgs = ['photo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label class="field-label">ID Proof (PAN/Aadhaar)</label>
                        <input type="file" name="id_proof" accept=".pdf,image/*"
                            class="field-input <?php echo e($errors->has('id_proof') ? 'has-error' : ''); ?>">
                        <p class="text-[10px] text-gray-400 mt-1">PDF, JPG, or PNG up to 5MB.</p>
                        <?php $__errorArgs = ['id_proof'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label class="field-label">Address Proof</label>
                        <input type="file" name="address_proof" accept=".pdf,image/*"
                            class="field-input <?php echo e($errors->has('address_proof') ? 'has-error' : ''); ?>">
                        <p class="text-[10px] text-gray-400 mt-1">PDF, JPG, or PNG up to 5MB.</p>
                        <?php $__errorArgs = ['address_proof'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                </div>
            </div>
        </div>

        
        <div class="sticky-footer">
            <a href="<?php echo e(route('admin.hrm.employees.index')); ?>"
                class="flex items-center justify-center px-5 py-2.5 rounded-xl text-[13px] font-bold text-gray-600 border border-gray-200 hover:bg-gray-50 transition-colors">
                Cancel
            </a>
            <button type="submit"
                class="flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-[14px] font-bold text-white transition-opacity hover:opacity-90"
                style="background: var(--brand-600)">
                <i data-lucide="check" style="width:16px;height:16px"></i>
                Save Employee
            </button>
        </div>

    </form>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/admin/hrm/employees/create.blade.php ENDPATH**/ ?>