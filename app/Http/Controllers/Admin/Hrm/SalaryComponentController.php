<?php

namespace App\Http\Controllers\Admin\Hrm;

use App\Http\Controllers\Controller;
use App\Models\Hrm\SalaryComponent;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SalaryComponentController extends Controller
{
    public function index(Request $request)
    {
        $components = SalaryComponent::ordered()
            ->paginate(50)
            ->withQueryString();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $components]);
        }

        return view('admin.hrm.salary-components.index', compact('components'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:30'],
            'type' => ['required', Rule::in(['earning', 'deduction'])],
            'description' => ['nullable', 'string'],
            'calculation_type' => ['required', Rule::in(['fixed', 'percentage'])],
            'percentage_of' => ['nullable', 'string', 'max:30'],
            'default_amount' => ['nullable', 'numeric', 'min:0'],
            'is_taxable' => ['boolean'],
            'is_statutory' => ['boolean'],
            'appears_on_payslip' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $component = SalaryComponent::create($validated);

        return response()->json(['success' => true, 'message' => 'Salary component created.', 'data' => $component]);
    }

    public function update(Request $request, SalaryComponent $salaryComponent)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:30'],
            'type' => ['required', Rule::in(['earning', 'deduction'])],
            'description' => ['nullable', 'string'],
            'calculation_type' => ['required', Rule::in(['fixed', 'percentage'])],
            'percentage_of' => ['nullable', 'string', 'max:30'],
            'default_amount' => ['nullable', 'numeric', 'min:0'],
            'is_taxable' => ['boolean'],
            'is_statutory' => ['boolean'],
            'appears_on_payslip' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $salaryComponent->update($validated);

        return response()->json(['success' => true, 'message' => 'Salary component updated.', 'data' => $salaryComponent]);
    }

    public function destroy(SalaryComponent $salaryComponent)
    {
        if ($salaryComponent->employeeStructures()->exists()) {
            return response()->json(['success' => false, 'message' => 'Cannot delete component in use by employee salary structures.'], 422);
        }

        $salaryComponent->delete();

        return response()->json(['success' => true, 'message' => 'Salary component deleted.']);
    }
}
