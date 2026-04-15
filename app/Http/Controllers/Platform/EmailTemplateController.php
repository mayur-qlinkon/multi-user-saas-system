<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmailTemplateController extends Controller
{
    /**
     * All known email template keys with metadata.
     * Add new keys here as the app grows.
     *
     * @var array<string, array{label: string, description: string, variables: string[]}>
     */
    public const DEFINED_TEMPLATES = [
        'order_inquiry_customer' => [
            'label' => 'Order Inquiry — Customer Confirmation',
            'description' => 'Sent to the customer after they submit a product inquiry.',
            'variables' => [
                'customer_name', 'customer_email', 'customer_phone',
                'product_name', 'message', 'store_name', 'order_number', 'inquiry_date',
            ],
        ],
        'order_inquiry_owner' => [
            'label' => 'Order Inquiry — Owner Notification',
            'description' => 'Sent to the store owner when a new inquiry arrives.',
            'variables' => [
                'customer_name', 'customer_email', 'customer_phone',
                'product_name', 'message', 'store_name', 'order_number', 'inquiry_date',
            ],
        ],
        'leave_request_owner' => [
            'label' => 'Leave Request — Admin Notification',
            'description' => 'Sent to targeted admins/owners when an employee requests leave.',
            'variables' => [
                // Removed the {} so Alpine.js handles the {{ }} wrapping cleanly
                'employee_name', 'leave_type', 'from_date', 'to_date',
                'total_days', 'reason', 'action_url',
            ],
        ],
    ];

    public function index(): View
    {
        $templates = EmailTemplate::whereNull('company_id')
            ->get()
            ->keyBy('key');

        return view('platform.email-templates', [
            'templates' => $templates,
            'defined' => self::DEFINED_TEMPLATES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:100', Rule::unique('email_templates', 'key')->whereNull('company_id')],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        EmailTemplate::create([
            'company_id' => null,
            'key' => $validated['key'],
            'subject' => $validated['subject'],
            'body' => $validated['body'],
        ]);

        return redirect()->route('platform.email-templates.index')
            ->with('success', 'Email template created successfully.');
    }

    public function update(Request $request, EmailTemplate $emailTemplate): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        $emailTemplate->update($validated);

        return redirect()->route('platform.email-templates.index')
            ->with('success', 'Email template updated successfully.');
    }

    public function destroy(EmailTemplate $emailTemplate): RedirectResponse
    {
        $emailTemplate->delete();

        return back()->with('success', 'Email template deleted.');
    }
}
