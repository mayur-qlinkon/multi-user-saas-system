<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        // Fetch all logs, eager loading the user who made the change (causer)
        $logs = Activity::with('causer')
            ->latest()
            ->paginate(50);

        return view('admin.audit-logs.index', compact('logs'));
    }
}
