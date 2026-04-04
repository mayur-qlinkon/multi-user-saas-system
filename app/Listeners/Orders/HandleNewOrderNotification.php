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

        // Find all internal users for this company who have owner or admin roles
        $admins = User::where('company_id', $order->company_id)
            ->whereHas('roles', function ($q) {
                $q->whereIn('slug', ['owner', 'admin']);
            })
            ->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new NewOrderInquiryNotification($order));
        }
    }
}