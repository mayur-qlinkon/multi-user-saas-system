<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;

class UpdateModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Ignore the current module ID for the unique check
            'name'      => ['required', 'string', 'max:150', 'unique:modules,name,' . $this->route('module')->id],
            'is_active' => ['boolean']
        ];
    }
}