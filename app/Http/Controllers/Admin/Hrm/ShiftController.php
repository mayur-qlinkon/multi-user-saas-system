<?php

namespace App\Http\Controllers\Admin\Hrm;

use App\Http\Controllers\Controller;
use App\Models\Hrm\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index(Request $request)
    {
        $shifts = Shift::withCount('employees')
            ->ordered()
            ->paginate(25)
            ->withQueryString();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $shifts]);
        }

        return view('admin.hrm.shifts.index', compact('shifts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'late_mark_after' => ['nullable', 'date_format:H:i'],
            'early_leave_before' => ['nullable', 'date_format:H:i'],
            'half_day_after' => ['nullable', 'date_format:H:i'],
            'break_duration_minutes' => ['nullable', 'integer', 'min:0'],
            'min_working_hours_minutes' => ['nullable', 'integer', 'min:0'],
            'overtime_after_minutes' => ['nullable', 'integer', 'min:0'],
            'is_night_shift' => ['boolean'],
            'is_default' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        if (! empty($validated['is_default'])) {
            Shift::where('is_default', true)->update(['is_default' => false]);
        }

        $shift = Shift::create($validated);

        return response()->json(['success' => true, 'message' => 'Shift created.', 'data' => $shift]);
    }

    public function update(Request $request, Shift $shift)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'late_mark_after' => ['nullable', 'date_format:H:i'],
            'early_leave_before' => ['nullable', 'date_format:H:i'],
            'half_day_after' => ['nullable', 'date_format:H:i'],
            'break_duration_minutes' => ['nullable', 'integer', 'min:0'],
            'min_working_hours_minutes' => ['nullable', 'integer', 'min:0'],
            'overtime_after_minutes' => ['nullable', 'integer', 'min:0'],
            'is_night_shift' => ['boolean'],
            'is_default' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        if (! empty($validated['is_default'])) {
            Shift::where('id', '!=', $shift->id)->where('is_default', true)->update(['is_default' => false]);
        }

        $shift->update($validated);

        return response()->json(['success' => true, 'message' => 'Shift updated.', 'data' => $shift]);
    }

    public function destroy(Shift $shift)
    {
        if ($shift->employees()->exists()) {
            return response()->json(['success' => false, 'message' => 'Cannot delete shift with assigned employees.'], 422);
        }

        $shift->delete();

        return response()->json(['success' => true, 'message' => 'Shift deleted.']);
    }
}
