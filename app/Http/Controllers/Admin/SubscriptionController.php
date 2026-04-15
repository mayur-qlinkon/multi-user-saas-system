<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySubscription;
use App\Models\Plan;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function index()
    {
        $companyId = Auth::user()->company_id;

        // 1. Fetch the active subscription for this specific company
        $currentSubscription = CompanySubscription::with('plan')
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->where('expires_at', '>', now()) // Ensure it hasn't expired
            ->latest()
            ->first();

        // 2. Fetch all active plans (and their modules) to display the pricing table
        $availablePlans = Plan::with('modules')
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc') // 🌟 Let Super Admin control the display order!
            ->get();

        return view('admin.subscription.index', compact('currentSubscription', 'availablePlans'));
    }
}
