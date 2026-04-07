<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\ContactInquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactInquiryController extends Controller
{
    /**
     * List all contact inquiries, newest first.
     */
    public function index(): View
    {
        $inquiries = ContactInquiry::latest('created_at')->paginate(25);

        return view('platform.inquiries.index', compact('inquiries'));
    }

    /**
     * Show a single inquiry and mark it as read.
     */
    public function show(ContactInquiry $contactInquiry): View
    {
        if (! $contactInquiry->is_read) {
            $contactInquiry->markRead();
        }

        return view('platform.inquiries.show', compact('contactInquiry'));
    }

    /**
     * Delete an inquiry permanently.
     */
    public function destroy(ContactInquiry $contactInquiry): RedirectResponse
    {
        $contactInquiry->delete();

        return redirect()->route('platform.inquiries.index')
            ->with('success', 'Inquiry deleted.');
    }
}
