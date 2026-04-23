<?php

namespace App\Services;

use App\Mail\TemplateMail;
use App\Models\Company;
use App\Models\EmailTemplate;
use App\Models\Order;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

/**
 * EmailService — Single entry point for all application email sending.
 *
 * Responsibilities:
 *   1. Resolve the correct template (tenant override → global fallback).
 *   2. Replace {{variable}} placeholders in subject and body.
 *   3. Apply runtime SMTP config from system_settings (Super Admin controlled).
 *   4. Enforce sender identity from system_settings — tenants cannot override this.
 *   5. Send via Laravel Mail and log success / failure.
 *
 * Usage:
 *   app(EmailService::class)->send('otp_verification', $email, $name, ['otp' => '123456'], $companyId);
 */
class EmailService
{
    /**
     * Name of the runtime mailer registered per-request in config.
     * Using a dedicated name avoids touching the global 'smtp' mailer config.
     */
    private const MAILER_NAME = 'system_smtp';

    // ════════════════════════════════════════════════════
    //  PUBLIC API
    // ════════════════════════════════════════════════════

    /**
     * Send order inquiry notification emails to both the customer and the store owner.
     * Silently skips if the recipient has no email address or no template is configured.
     * Never throws — email failure must never break the order flow.
     */
    public function sendOrderInquiryEmails(Order $order, Company $company, string $productName = ''): void
    {
        $variables = [
            'customer_name' => $order->customer_name ?? '',
            'customer_email' => $order->customer_email ?? '',
            'customer_phone' => $order->customer_phone ?? '',
            'product_name' => $productName,
            'message' => $order->customer_notes ?? '',
            'store_name' => $company->name,
            'order_number' => $order->order_number,
            'inquiry_date' => $order->created_at?->format('d M Y, h:i A') ?? now()->format('d M Y, h:i A'),
        ];

        // Send confirmation to customer (only if they provided an email)
        if (! empty($order->customer_email)) {
            $this->send('order_inquiry_customer', $order->customer_email, $order->customer_name ?? 'Customer', $variables);
        }

        // Notify the store owner
        if (! empty($company->email)) {
            $this->send('order_inquiry_owner', $company->email, $company->name, $variables);
        }
    }

    /**
     * Send an email using a stored template.
     *
     * @param  string  $templateKey  Template identifier, e.g. 'password_reset'
     * @param  string  $toEmail  Recipient email address
     * @param  string  $toName  Recipient display name
     * @param  array<string,string>  $variables  Replacement map, e.g. ['otp' => '123456']
     * @param  int|null  $companyId  Tenant ID for template resolution (null = global only)
     */
    public function send(
        string $templateKey,
        string $toEmail,
        string $toName,
        array $variables = [],
        ?int $companyId = null,
    ): void {
        Log::info('[EmailService] Preparing email', [
            'template' => $templateKey,
            'to' => $toEmail,
            'company_id' => $companyId,
        ]);

        try {
            // 1. Load and validate SMTP config from system_settings.
            $mailConfig = $this->loadMailConfig();

            if (! $this->isConfigComplete($mailConfig)) {
                Log::error('[EmailService] Aborted — incomplete SMTP configuration in system_settings', [
                    'template' => $templateKey,
                    'to' => $toEmail,
                    'missing' => $this->missingConfigKeys($mailConfig),
                ]);

                return;
            }

            // 2. Resolve template (tenant → global fallback).
            $template = $this->resolveTemplate($templateKey, $companyId);

            // 3. Replace {{variable}} placeholders.
            $subject = $this->replaceVariables($template->subject, $variables);
            $body = $this->replaceVariables($template->body, $variables);

            // 4. Register runtime mailer with system_settings SMTP values.
            $this->applyRuntimeMailer($mailConfig);

            // 5. Send — sender identity is always from system_settings, never tenant-controlled.
            Mail::mailer(self::MAILER_NAME)
                ->to($toEmail, $toName)
                ->send(new TemplateMail($subject, $body));

            Log::info('[EmailService] Email sent successfully', [
                'template' => $templateKey,
                'to' => $toEmail,
                'company_id' => $companyId,
            ]);

        } catch (RuntimeException $e) {
            // Template resolution failures — log clearly so developers can seed missing templates.
            Log::error('[EmailService] Template not found', [
                'template' => $templateKey,
                'company_id' => $companyId,
                'error' => $e->getMessage(),
            ]);

        } catch (\Throwable $e) {
            Log::error('[EmailService] Failed to send email', [
                'template' => $templateKey,
                'to' => $toEmail,
                'company_id' => $companyId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    // ════════════════════════════════════════════════════
    //  PRIVATE — TEMPLATE RESOLUTION
    // ════════════════════════════════════════════════════

    /**
     * Resolve the correct template using a two-step fallback:
     *   1. Tenant-specific template (company_id = $companyId)
     *   2. Global fallback template (company_id IS NULL)
     *
     * @throws RuntimeException if no template exists for the given key
     */
    private function resolveTemplate(string $key, ?int $companyId): EmailTemplate
    {
        // Step 1 — try tenant override.
        if ($companyId !== null) {
            $template = EmailTemplate::where('key', $key)
                ->where('company_id', $companyId)
                ->first();

            if ($template !== null) {
                return $template;
            }
        }

        // Step 2 — fall back to global template.
        $global = EmailTemplate::where('key', $key)
            ->whereNull('company_id')
            ->first();

        if ($global !== null) {
            return $global;
        }

        // Step 3 — Fallback to hardcoded defaults so the system never crashes
        Log::warning("[EmailService] Using hardcoded fallback for template: {$key}");
        
        return $this->getHardcodedFallbackTemplate($key);
    }

    /**
     * Provide an in-memory fallback template if neither tenant nor global templates are seeded.
     */
    private function getHardcodedFallbackTemplate(string $key): EmailTemplate
    {
        $defaults = [
            'order_inquiry_customer' => [
                'subject' => 'We received your inquiry, {{customer_name}}!',
                'body'    => '<p>Hi {{customer_name}},</p><p>Thank you for inquiring about <strong>{{product_name}}</strong>. Our team at {{store_name}} will get back to you shortly.</p><p>Reference: {{order_number}}</p>',
            ],
            'order_inquiry_owner' => [
                'subject' => 'New Inquiry from {{customer_name}} - {{order_number}}',
                'body'    => '<p><strong>New Inquiry Received</strong></p><p>Customer: {{customer_name}}<br>Phone: {{customer_phone}}<br>Email: {{customer_email}}</p><p>Product: {{product_name}}</p><p>Message:<br>{{message}}</p>',
            ],
            'leave_request_owner' => [
                'subject' => 'New Leave Request: {{employee_name}}',
                'body'    => '<p><strong>{{employee_name}}</strong> has requested {{leave_type}} leave from {{from_date}} to {{to_date}} ({{total_days}} days).</p><p>Reason: {{reason}}</p><p><a href="{{action_url}}">View Request</a></p>',
            ]
        ];

        // If the key is totally unknown, provide a generic safe default
        $subject = $defaults[$key]['subject'] ?? 'Notification: ' . ucwords(str_replace('_', ' ', $key));
        $body    = $defaults[$key]['body'] ?? '<p>You have a new system notification.</p>';

        return new EmailTemplate([
            'key'     => $key,
            'subject' => $subject,
            'body'    => $body,
        ]);
    }

    // ════════════════════════════════════════════════════
    //  PRIVATE — VARIABLE REPLACEMENT
    // ════════════════════════════════════════════════════

    /**
     * Replace all {{variable}} placeholders in a string.
     *
     * @param  array<string,string>  $variables
     */
    private function replaceVariables(string $content, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $content = str_replace('{{'.$key.'}}', (string) $value, $content);
        }

        return $content;
    }

    // ════════════════════════════════════════════════════
    //  PRIVATE — MAIL CONFIGURATION
    // ════════════════════════════════════════════════════

    /**
     * Fetch SMTP settings from system_settings (Super Admin controlled).
     * SystemSetting::getSetting() uses rememberForever caching, so repeated
     * calls within a request incur only one cache lookup.
     *
     * @return array<string,mixed>
     */
    private function loadMailConfig(): array
    {
        return [
            'driver'     => get_system_setting('mail_driver', config('mail.default', env('MAIL_MAILER', 'smtp'))),
            'host'       => get_system_setting('mail_host', config('mail.mailers.smtp.host', env('MAIL_HOST'))),
            'port'       => (int) (get_system_setting('mail_port') ?: config('mail.mailers.smtp.port', env('MAIL_PORT', 587))),
            'username'   => get_system_setting('mail_username', config('mail.mailers.smtp.username', env('MAIL_USERNAME'))),
            'password'   => get_system_setting('mail_password', config('mail.mailers.smtp.password', env('MAIL_PASSWORD'))),
            'encryption' => get_system_setting('mail_encryption', config('mail.mailers.smtp.encryption', env('MAIL_ENCRYPTION', 'tls'))),
            'from_email' => get_system_setting('mail_from_email', config('mail.from.address', env('MAIL_FROM_ADDRESS'))),
            'from_name'  => get_system_setting('mail_from_name') ?: config('mail.from.name', env('MAIL_FROM_NAME', config('app.name'))),
        ];
    }

    /**
     * Minimum required keys that must be non-empty before attempting to send.
     *
     * @param  array<string,mixed>  $config
     */
    private function isConfigComplete(array $config): bool
    {
        return ! empty($config['host'])
            && ! empty($config['username'])
            && ! empty($config['from_email']);
    }

    /**
     * Return the list of missing required keys, used for structured error logging.
     *
     * @param  array<string,mixed>  $config
     * @return string[]
     */
    private function missingConfigKeys(array $config): array
    {
        return array_values(
            array_filter(['host', 'username', 'from_email'], fn ($k) => empty($config[$k]))
        );
    }

    /**
     * Register a named runtime mailer in Laravel's config so we can call
     * Mail::mailer('system_smtp'). Using a dedicated name leaves the global
     * 'smtp' / 'log' mailers from .env completely untouched.
     *
     * Sender identity (from address & name) is always set from system_settings
     * here — tenant code never has access to override it.
     *
     * @param  array<string,mixed>  $mailConfig
     */
    private function applyRuntimeMailer(array $mailConfig): void
    {
        Config::set('mail.mailers.'.self::MAILER_NAME, [
            'transport' => $mailConfig['driver'] ?: 'smtp',
            'host' => $mailConfig['host'],
            'port' => $mailConfig['port'],
            'username' => $mailConfig['username'],
            'password' => $mailConfig['password'],
            'encryption' => $mailConfig['encryption'] ?: null,
        ]);

        // Sender identity — Super Admin controlled, enforced here on every send.
        Config::set('mail.from.address', $mailConfig['from_email']);
        Config::set('mail.from.name', $mailConfig['from_name']);
    }
}
