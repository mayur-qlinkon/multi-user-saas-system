<?php

namespace App\Http\Controllers\Admin\Hrm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Hrm\StoreEmployeeRequest;
use App\Http\Requests\Admin\Hrm\UpdateEmployeeRequest;
use App\Models\Hrm\Department;
use App\Models\Hrm\Designation;
use App\Models\Hrm\Employee;
use App\Models\Hrm\EmployeeSalaryStructure;
use App\Models\Hrm\SalaryComponent;
use App\Models\Hrm\Shift;
use App\Models\Store;
use App\Models\User;
use App\Services\Hrm\EmployeeService;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function __construct(
        protected EmployeeService $employeeService
    ) {}

    public function index(Request $request)
    {
        $query = Employee::with(['user', 'department', 'designation', 'store']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('employee_code', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        $employees = $query->orderBy('employee_code')
            ->paginate(25)
            ->withQueryString();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $employees]);
        }

        $departments = Department::active()->ordered()->get();
        $designations = Designation::active()->ordered()->get();
        $stores = Store::where('is_active', true)->get();
        $canAddMore = check_plan_limit('employees');

        return view('admin.hrm.employees.index', compact('employees', 'departments', 'designations', 'stores', 'canAddMore'));
    }

    public function create()
    {
        if (! check_plan_limit('employees')) {
            return redirect()->route('admin.hrm.employees.index')
                ->with('error', 'You have reached your subscription limit for employees. Please upgrade your plan to add more.');
        }

        $departments = Department::active()->ordered()->get();
        $designations = Designation::active()->ordered()->get();
        $shifts = Shift::active()->ordered()->get();
        $stores = Store::where('is_active', true)->get();
        $managers = Employee::active()->with('user')->get();

        // Users not yet linked as employees (include old value on validation redirect)
        $availableUsers = User::query()
            ->internal()
            ->where(function ($query) {
                $query->whereDoesntHave('employee');
                
                // Keep the selected user in the list if validation fails
                if (old('user_id')) {
                    $query->orWhere('id', old('user_id'));
                }
            })
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('admin.hrm.employees.create', compact(
            'departments', 'designations', 'shifts', 'stores', 'managers', 'availableUsers'
        ));
    }

    public function store(StoreEmployeeRequest $request)
    {
        if (! check_plan_limit('employees')) {
            $message = 'Employee limit reached for your current plan. Please upgrade to add more employees.';

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->withErrors(['error' => $message]);
        }

        try {
            $employee = $this->employeeService->create($request->validated());

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Employee created.', 'data' => $employee]);
            }

            return redirect()->route('admin.hrm.employees.show', $employee)
                ->with('success', 'Employee created successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function show(Employee $employee)
    {
        $employee->load([
            'user', 'user.stores', 'department', 'designation', 'shift', 'store',
            'reportingManager.user', 'subordinates.user',
        ]);

        $salaryStructures = EmployeeSalaryStructure::where('employee_id', $employee->id)
            ->with('salaryComponent')
            ->orderBy('is_active', 'desc')
            ->get();

        $salaryComponents = SalaryComponent::where('is_active', true)
            ->orderBy('type')
            ->orderBy('sort_order')
            ->get();

        return view('admin.hrm.employees.show', compact('employee', 'salaryStructures', 'salaryComponents'));
    }

    public function salaryStructures(Employee $employee)
    {
        $structures = EmployeeSalaryStructure::where('employee_id', $employee->id)
            ->with('salaryComponent')
            ->orderBy('is_active', 'desc')
            ->get();

        return response()->json(['data' => $structures]);
    }

    public function storeSalaryStructure(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'salary_component_id' => 'required|exists:salary_components,id',
            'calculation_type' => 'required|in:fixed,percentage',
            'amount' => 'nullable|numeric|min:0',
            'percentage_of' => 'nullable|string|max:50',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
        ]);

        $validated['employee_id'] = $employee->id;
        $validated['is_active'] = true;
        // Default effective_from to start of current month so it covers the current payroll period
        if (empty($validated['effective_from'])) {
            $validated['effective_from'] = now()->startOfMonth()->toDateString();
        }

        // Deactivate any existing structure for same component (avoid duplicates)
        EmployeeSalaryStructure::where('employee_id', $employee->id)
            ->where('salary_component_id', $validated['salary_component_id'])
            ->where('is_active', true)
            ->update(['is_active' => false]);

        $structure = EmployeeSalaryStructure::create($validated);
        $structure->load('salaryComponent');

        return response()->json(['message' => 'Component added to salary structure.', 'data' => $structure], 201);
    }

    public function destroySalaryStructure(Employee $employee, EmployeeSalaryStructure $structure)
    {
        abort_if($structure->employee_id !== $employee->id, 403);
        $structure->delete();

        return response()->json(['message' => 'Component removed from salary structure.']);
    }

    public function edit(Employee $employee)
    {
        $employee->load(['user', 'department', 'designation', 'store']);

        $departments = Department::active()->ordered()->get();
        $designations = Designation::active()->ordered()->get();
        $shifts = Shift::active()->ordered()->get();
        $stores = Store::where('is_active', true)->get();
        $managers = Employee::active()->where('id', '!=', $employee->id)->with('user')->get();

        return view('admin.hrm.employees.edit', compact(
            'employee', 'departments', 'designations', 'shifts', 'stores', 'managers'
        ));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        try {
            $employee = $this->employeeService->update($employee, $request->validated());

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Employee updated.', 'data' => $employee]);
            }

            return redirect()->route('admin.hrm.employees.show', $employee)
                ->with('success', 'Employee updated successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function destroy(Employee $employee)
    {
        try {
            $this->employeeService->delete($employee);

            return response()->json(['success' => true, 'message' => 'Employee deleted.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
