<?php

namespace App\Repositories;

use App\Models\CompanySubscription;
use App\Models\Company;
use App\Models\Plan;

class CompanySubscriptionRepository
{
    public function getAllSubscriptions()
    {
        // Eager load the related company and plan models
        return CompanySubscription::with(['company', 'plan'])->latest()->get();
    }

    public function getActiveCompanies()
    {
        return Company::orderBy('name')->get();
    }

    public function getActivePlans()
    {
        return Plan::where('is_active', true)->orderBy('price')->get();
    }

    public function assignOrUpdateSubscription(array $data): CompanySubscription
    {
        // This will update the company's subscription if they already have one, 
        // or create a new one if they don't.
        return CompanySubscription::updateOrCreate(
            ['company_id' => $data['company_id']],
            [
                'plan_id'    => $data['plan_id'],
                'starts_at'  => $data['starts_at'] ?? now(),
                'expires_at' => $data['expires_at'] ?? null,
                'is_active'  => $data['is_active'] ?? true,
            ]
        );
    }
}