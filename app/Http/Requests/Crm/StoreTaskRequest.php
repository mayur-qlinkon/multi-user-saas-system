<?php

// ════════════════════════════════════════════════════
//  FILE: app/Http/Requests/Crm/StoreTaskRequest.php
// ════════════════════════════════════════════════════

namespace App\Http\Requests\Crm;

use App\Models\User;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = (int) $this->user()->company_id;

        return [
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type' => ['required', Rule::in(['follow_up', 'call', 'meeting', 'whatsapp', 'email', 'demo', 'other'])],
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high'])],
            'due_at' => ['required', 'date', 'after:now'],
            'assigned_to' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where('company_id', $companyId),
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (blank($value)) {
                        return;
                    }

                    $isAssignableUser = User::query()
                        ->internal()
                        ->whereKey($value)
                        ->exists();

                    if (! $isAssignableUser) {
                        $fail('Selected assignee must be a staff user.');
                    }
                },
            ],
            'remind_at' => ['nullable', 'date', 'before:due_at'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Task title is required.',
            'type.required' => 'Task type is required.',
            'due_at.required' => 'Due date is required.',
            'due_at.after' => 'Due date must be in the future.',
            'remind_at.before' => 'Reminder must be before the due date.',
        ];
    }
}
