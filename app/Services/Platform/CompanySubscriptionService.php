<?php

namespace App\Services\Platform;

use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\Plan;

class CompanySubscriptionService
{
    public function getIndexData(): array
    {
        return [
            'subscriptions' => CompanySubscription::with(['company', 'plan'])->latest()->get(),
            'companies' => Company::orderBy('name')->get(),
            'plans' => Plan::where('is_active', true)->orderBy('price')->get(),
        ];
    }

    public function assignSubscription(array $data): CompanySubscription
    {
        $data['is_active'] = $data['is_active'] ?? false;

        return CompanySubscription::updateOrCreate(
            ['company_id' => $data['company_id']],
            [
                'plan_id' => $data['plan_id'],
                'starts_at' => $data['starts_at'] ?? now(),
                'expires_at' => $data['expires_at'] ?? null,
                'is_active' => $data['is_active'],
            ]
        );
    }
}
