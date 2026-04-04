<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

/**
 * SystemSettingsSeeder
 *
 * Seeds all platform-level system settings with safe defaults.
 * Run once on fresh install:
 *   php artisan db:seed --class=SystemSettingsSeeder
 *
 * Safe to re-run — uses firstOrCreate logic via setSetting()
 * so existing customized values are NEVER overwritten.
 */
class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [

            // ── GENERAL ──────────────────────────────────────
            'general' => [
                'app_name'           => ['value' => 'Qlinkon BIZNESS', 'type' => 'string'],
                'support_email'      => ['value' => '',                'type' => 'string'],
                'support_whatsapp'   => ['value' => '',                'type' => 'string'],
                'support_phone'      => ['value' => '',                'type' => 'string'],
            ],

            // ── MAINTENANCE ───────────────────────────────────
            'maintenance' => [
                'maintenance_mode'    => ['value' => '0', 'type' => 'boolean'],
                'maintenance_message' => [
                    'value' => 'We are currently performing scheduled maintenance. We\'ll be back shortly!',
                    'type'  => 'string',
                ],
            ],

            // ── FEATURES (platform-wide on/off) ──────────────
            // Default: all enabled (1)
            // Super admin can disable any feature for ALL tenants
            'features' => [
                'feature_crm'        => ['value' => '1', 'type' => 'boolean'],
                'feature_pos'        => ['value' => '1', 'type' => 'boolean'],
                'feature_storefront' => ['value' => '1', 'type' => 'boolean'],
                'feature_hrm'        => ['value' => '1', 'type' => 'boolean'],
                'feature_reports'    => ['value' => '1', 'type' => 'boolean'],
                'feature_purchases'  => ['value' => '1', 'type' => 'boolean'],
            ],

            // ── BILLING / LOCKOUT ─────────────────────────────
            'billing' => [
                'lockout_mode'              => ['value' => '0',  'type' => 'boolean'],
                'grace_period_days'         => ['value' => '3',  'type' => 'integer'],
                'default_trial_days'        => ['value' => '14', 'type' => 'integer'],
                'allow_new_registrations'   => ['value' => '1',  'type' => 'boolean'],
                'lockout_message'           => [
                    'value' => 'Your subscription has expired. Please renew to continue.',
                    'type'  => 'string',
                ],
            ],

            // ── ANNOUNCEMENTS ─────────────────────────────────
            'announcements' => [
                'platform_announcement_active' => ['value' => '0', 'type' => 'boolean'],
                'platform_announcement_text'   => ['value' => '', 'type' => 'string'],
                'platform_announcement_type'   => ['value' => 'info', 'type' => 'string'],
                // type: info | warning | success | danger
            ],

            // ── SECURITY ──────────────────────────────────────
            'security' => [
                'force_email_verification' => ['value' => '0', 'type' => 'boolean'],
                'enable_2fa'               => ['value' => '0', 'type' => 'boolean'],
            ],

        ];

        foreach ($defaults as $group => $settings) {
            foreach ($settings as $key => $config) {
                // Only insert if key does NOT exist — never overwrite existing values
                SystemSetting::firstOrCreate(
                    ['key' => $key],
                    [
                        'value' => $config['value'],
                        'group' => $group,
                        'type'  => $config['type'],
                    ]
                );
            }
        }

        // Clear cache after seeding
        Cache::forget(SystemSetting::CACHE_KEY);

        $this->command->info('[SystemSettingsSeeder] Platform settings seeded successfully.');
    }
}