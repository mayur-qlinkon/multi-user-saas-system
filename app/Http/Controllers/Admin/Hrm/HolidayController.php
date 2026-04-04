<?php

namespace App\Http\Controllers\Admin\Hrm;

use App\Http\Controllers\Controller;
use App\Models\Hrm\Holiday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $query = Holiday::query();

        if ($request->filled('year')) {
            $query->forYear($request->year);
        } else {
            $query->forYear(now()->year);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $holidays = $query->orderBy('date')
            ->paginate(25)
            ->withQueryString();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $holidays]);
        }

        $stats = [
            'total' => Holiday::forYear($request->input('year', now()->year))->active()->count(),
            'upcoming' => Holiday::upcoming()->active()->count(),
            'national' => Holiday::forYear($request->input('year', now()->year))->where('type', 'national')->count(),
            'company' => Holiday::forYear($request->input('year', now()->year))->where('type', 'company')->count(),
        ];

        return view('admin.hrm.holidays.index', compact('holidays', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:date'],
            'type' => ['required', Rule::in(['national', 'state', 'company', 'restricted', 'optional'])],
            'description' => ['nullable', 'string'],
            'is_paid' => ['boolean'],
            'is_recurring' => ['boolean'],
            'is_active' => ['boolean'],
            'applicable_departments' => ['nullable', 'array'],
            'applicable_stores' => ['nullable', 'array'],
        ]);

        $validated['created_by'] = Auth::id();

        $holiday = Holiday::create($validated);

        return response()->json(['success' => true, 'message' => 'Holiday created.', 'data' => $holiday]);
    }

    public function update(Request $request, Holiday $holiday)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:date'],
            'type' => ['required', Rule::in(['national', 'state', 'company', 'restricted', 'optional'])],
            'description' => ['nullable', 'string'],
            'is_paid' => ['boolean'],
            'is_recurring' => ['boolean'],
            'is_active' => ['boolean'],
            'applicable_departments' => ['nullable', 'array'],
            'applicable_stores' => ['nullable', 'array'],
        ]);

        $holiday->update($validated);

        return response()->json(['success' => true, 'message' => 'Holiday updated.', 'data' => $holiday]);
    }

    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

        return response()->json(['success' => true, 'message' => 'Holiday deleted.']);
    }
}
