<?php

namespace App\Listeners\Orders;

use App\Events\Orders\OrderPlaced;
use App\Models\User;
use App\Notifications\Orders\NewOrderInquiryNotification;
use Illuminate\Support\Facades\Notification;

class HandleNewOrderNotification
{
    public function handle(OrderPlaced $event): void
    {
        $order = $event->order;
        $companyId = $order->company_id;

        // Load config from settings table using explicit company_id — safe for queued listeners.
        $raw = get_setting('notify_new_order', null, $companyId);
        $config = $raw ? json_decode($raw, true) : null;

        // Fallback: notify the owner role when nothing is configured.
        $roleSlugs = ! empty($config['roles']) ? $config['roles'] : ['owner'];
        $userIds = ! empty($config['users']) ? array_map('intval', $config['users']) : [];

        // Collect users by configured roles — single query.
        $byRole = User::where('company_id', $companyId)
            ->whereHas('roles', fn ($q) => $q->whereIn('slug', $roleSlugs))
            ->get();

        // Collect explicitly listed users — only runs if list is non-empty.
        $byId = $userIds
            ? User::where('company_id', $companyId)->whereIn('id', $userIds)->get()
            : collect();

        $recipients = $byRole->merge($byId)->unique('id');

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new NewOrderInquiryNotification($order));
        }
    }
}
