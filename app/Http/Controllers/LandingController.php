<?php

namespace App\Http\Controllers;

use App\Models\ContactInquiry;
use App\Services\EmailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LandingController extends Controller
{
    /**
     * Show the public landing page.
     */
    public function index(): View
    {
        return view('landing');
    }

    /**
     * Handle the contact form submission.
     */
    public function inquire(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $inquiry = ContactInquiry::create($validated);

        // Notify super admin via email if template exists.
        $adminEmail = get_system_setting('support_email');
        if ($adminEmail) {
            app(EmailService::class)->send(
                'inquiry_received',
                $adminEmail,
                get_system_setting('app_name', 'Platform Admin'),
                [
                    'name' => $inquiry->name,
                    'email' => $inquiry->email,
                    'phone' => $inquiry->phone ?? 'N/A',
                    'message' => $inquiry->message,
                ],
            );
        }

        return redirect()->route('landing')->with('success', 'Thank you! We will get back to you soon.');
    }
}
