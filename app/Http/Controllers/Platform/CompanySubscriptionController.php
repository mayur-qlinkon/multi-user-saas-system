<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\AssignSubscriptionRequest;
use App\Services\Platform\CompanySubscriptionService;

class CompanySubscriptionController extends Controller
{
    protected CompanySubscriptionService $subscriptionService;

    public function __construct(CompanySubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Display the subscriptions overview and modal data.
     */
    public function index()
    {
        $data = $this->subscriptionService->getIndexData();

        return view('platform.subscriptions', $data);
    }

    /**
     * Assign or update a subscription for a company.
     */
    public function assign(AssignSubscriptionRequest $request)
    {
        $this->subscriptionService->assignSubscription($request->validated());

        return redirect()->route('platform.subscriptions.index')
            ->with('success', 'Subscription assigned successfully.');
    }
}
