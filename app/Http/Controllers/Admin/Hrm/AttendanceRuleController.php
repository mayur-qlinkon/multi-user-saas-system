<?php

namespace App\Http\Controllers\Admin\Hrm;

use App\Http\Controllers\Controller;
use App\Models\Hrm\AttendanceRule;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendanceRuleController extends Controller
{
    public function index(Request $request)
    {
        $rules = AttendanceRule::ordered()
            ->paginate(25)
            ->withQueryString();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $rules]);
        }

        return view('admin.hrm.attendance-rules.index', compact('rules'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string'],
            'rule_type' => ['required', Rule::in(array_keys(AttendanceRule::TYPE_LABELS))],
            'threshold_count' => ['required', 'integer', 'min:1'],
            'threshold_period' => ['required', Rule::in(array_keys(AttendanceRule::PERIOD_LABELS))],
            'action' => ['required', Rule::in(array_keys(AttendanceRule::ACTION_LABELS))],
            'deduction_days' => ['nullable', 'numeric', 'min:0'],
            'leave_type_code' => ['nullable', 'string', 'max:20'],
            'auto_apply' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $rule = AttendanceRule::create($validated);

        return response()->json(['success' => true, 'message' => 'Attendance rule created.', 'data' => $rule]);
    }

    public function update(Request $request, AttendanceRule $attendanceRule)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string'],
            'rule_type' => ['required', Rule::in(array_keys(AttendanceRule::TYPE_LABELS))],
            'threshold_count' => ['required', 'integer', 'min:1'],
            'threshold_period' => ['required', Rule::in(array_keys(AttendanceRule::PERIOD_LABELS))],
            'action' => ['required', Rule::in(array_keys(AttendanceRule::ACTION_LABELS))],
            'deduction_days' => ['nullable', 'numeric', 'min:0'],
            'leave_type_code' => ['nullable', 'string', 'max:20'],
            'auto_apply' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $attendanceRule->update($validated);

        return response()->json(['success' => true, 'message' => 'Attendance rule updated.', 'data' => $attendanceRule]);
    }

    public function destroy(AttendanceRule $attendanceRule)
    {
        $attendanceRule->delete();

        return response()->json(['success' => true, 'message' => 'Attendance rule deleted.']);
    }
}
