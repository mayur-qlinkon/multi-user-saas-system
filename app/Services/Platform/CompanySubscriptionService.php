<?php

namespace App\Services\Platform;

use App\Models\CompanySubscription;
use App\Repositories\CompanySubscriptionRepository;

class CompanySubscriptionService
{
    protected CompanySubscriptionRepository $subscriptionRepo;

    public function __construct(CompanySubscriptionRepository $subscriptionRepo)
    {
        $this->subscriptionRepo = $subscriptionRepo;
    }

    public function getIndexData(): array
    {
        return [
            'subscriptions' => $this->subscriptionRepo->getAllSubscriptions(),
            'companies'     => $this->subscriptionRepo->getActiveCompanies(),
            'plans'         => $this->subscriptionRepo->getActivePlans()
        ];
    }

    public function assignSubscription(array $data): CompanySubscription
    {
        // Default is_active to false if unchecked
        $data['is_active'] = $data['is_active'] ?? false;

        return $this->subscriptionRepo->assignOrUpdateSubscription($data);
    }
}