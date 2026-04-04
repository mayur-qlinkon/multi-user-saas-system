<?php

namespace App\Http\Controllers\Admin\Hrm;

use App\Http\Controllers\Controller;
use App\Models\Hrm\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeaveTypeController extends Controller
{
    public function index(Request $request)
    {
        $leaveTypes = LeaveType::ordered()
            ->paginate(25)
            ->withQueryString();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $leaveTypes]);
        }

        return view('admin.hrm.leave-types.index', compact('leaveTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
            'default_days_per_year' => ['required', 'numeric', 'min:0'],
            'is_paid' => ['boolean'],
            'is_carry_forward' => ['boolean'],
            'max_carry_forward_days' => ['nullable', 'numeric', 'min:0'],
            'is_encashable' => ['boolean'],
            'requires_document' => ['boolean'],
            'min_days_before_apply' => ['nullable', 'integer', 'min:0'],
            'max_consecutive_days' => ['nullable', 'numeric', 'min:0'],
            'applicable_gender' => ['required', Rule::in(['all', 'male', 'female'])],
            'is_active' => ['boolean'],
        ]);

        $leaveType = LeaveType::create($validated);

        return response()->json(['success' => true, 'message' => 'Leave type created.', 'data' => $leaveType]);
    }

    public function update(Request $request, LeaveType $leaveType)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
            'default_days_per_year' => ['required', 'numeric', 'min:0'],
            'is_paid' => ['boolean'],
            'is_carry_forward' => ['boolean'],
            'max_carry_forward_days' => ['nullable', 'numeric', 'min:0'],
            'is_encashable' => ['boolean'],
            'requires_document' => ['boolean'],
            'min_days_before_apply' => ['nullable', 'integer', 'min:0'],
            'max_consecutive_days' => ['nullable', 'numeric', 'min:0'],
            'applicable_gender' => ['required', Rule::in(['all', 'male', 'female'])],
            'is_active' => ['boolean'],
        ]);

        $leaveType->update($validated);

        return response()->json(['success' => true, 'message' => 'Leave type updated.', 'data' => $leaveType]);
    }

    public function destroy(LeaveType $leaveType)
    {
        if ($leaveType->leaves()->exists()) {
            return response()->json(['success' => false, 'message' => 'Cannot delete leave type with existing leave records.'], 422);
        }

        $leaveType->delete();

        return response()->json(['success' => true, 'message' => 'Leave type deleted.']);
    }
}
