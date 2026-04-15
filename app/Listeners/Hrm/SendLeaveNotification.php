<?php

namespace App\Listeners\Hrm;

use App\Events\Hrm\LeaveRequested;
use App\Models\User;
use App\Notifications\Hrm\LeaveRequestedNotification;
use App\Services\EmailService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendLeaveNotification
{
    public function handle(LeaveRequested $event): void
    {
        $leave = $event->leave;
        $companyId = $leave->company_id;

        // 1. Load config
        $raw = get_setting('notify_leave_request', null, $companyId);
        $config = $raw ? json_decode($raw, true) : null;

        $roleSlugs = ! empty($config['roles']) ? $config['roles'] : [];
        $userIds = ! empty($config['users']) ? array_map('intval', $config['users']) : [];

        if (empty($roleSlugs) && empty($userIds)) {
            $roleSlugs = ['owner'];
        }

        // 2. Query by roles & user IDs
        $byRole = $roleSlugs
            ? User::where('company_id', $companyId)
                ->whereHas('roles', fn ($q) => $q->whereIn('slug', $roleSlugs))
                ->get()
            : collect();

        $byId = $userIds
            ? User::where('company_id', $companyId)
                ->whereIn('id', $userIds)
                ->get()
            : collect();

        // 3. Merge, unique, and strictly filter valid emails
        $recipients = $byRole->merge($byId)->unique('id')->filter(function ($user) {
            return filter_var($user->email, FILTER_VALIDATE_EMAIL) !== false;
        })->values();

        if ($recipients->isEmpty()) {
            Log::warning('[SendLeaveNotification] No valid recipients found — aborted.');

            return;
        }

        // 4. Send Database Notification
        Notification::send($recipients, new LeaveRequestedNotification($leave));

        // 5. Prepare Variables for EmailService
        $employeeName = $leave->employee->full_name ?: 'Unknown Employee';
        $leaveType = $leave->leaveType->name ?? 'Leave';
        $actionUrl = url('/admin/hrm/leaves/'.$leave->id);

        $fromDate = $leave->from_date instanceof Carbon
            ? $leave->from_date->format('d M Y')
            : Carbon::parse($leave->from_date)->format('d M Y');

        $toDate = $leave->to_date instanceof Carbon
            ? $leave->to_date->format('d M Y')
            : Carbon::parse($leave->to_date)->format('d M Y');

        // Notice: No {{ }} around the keys. EmailService adds them automatically!
        $variables = [
            'employee_name' => $employeeName,
            'leave_type' => $leaveType,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'total_days' => $leave->total_days,
            'reason' => $leave->reason ?: 'No reason provided',
            'action_url' => $actionUrl,
        ];

        // 6. Send Emails via EmailService
        foreach ($recipients as $recipient) {
            try {
                app(EmailService::class)->send(
                    'leave_request_owner', // template key
                    $recipient->email,     // toEmail
                    $recipient->name,      // toName
                    $variables,            // variables map
                    $companyId             // company_id for tenant override
                );
            } catch (\Exception $e) {
                Log::error('[SendLeaveNotification] EmailService failed', [
                    'email' => $recipient->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
