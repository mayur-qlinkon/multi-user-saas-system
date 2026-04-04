<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\State;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StoreController extends Controller
{
    protected ImageUploadService $imageService;

    public function __construct(ImageUploadService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * List all stores for the logged-in owner
     */
    public function index()
    {
        // Fetching stores belonging to the authenticated user's company
        $stores = Store::where('company_id', auth()->user()->company_id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Pass states for the new dynamic state dropdown
        $states = State::where('is_active', true)->orderBy('name')->get();

        return view('admin.stores', compact('stores', 'states'));
    }

    /**
     * Store a new shop/branch
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'nullable|email|max:255',
            'phone'           => 'nullable|string|max:20',
            'upi_id'           => 'nullable|string',
            'gst_number'      => 'nullable|string|max:15',
            'state_id'        => 'required|exists:states,id', // 🌟 NEW: Strict Validation
            'city'            => 'nullable|string|max:100',
            'address'         => 'nullable|string',
            'zip_code'        => 'nullable|string|max:20',
            'invoice_prefix'  => 'nullable|string|max:10', // 🌟 NEW
            'purchase_prefix' => 'nullable|string|max:10', // 🌟 NEW
            'logo_file'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:1024',
            'signature_file'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:1024', // 🌟 NEW
        ]);

        $data = $request->except(['logo_file', 'signature_file', '_token']);
        $data['company_id'] = auth()->user()->company_id;
        $data['is_active'] = $request->boolean('is_active', true);

        // 🌟 NEW: Process Images using our robust Service (Converts to WebP automatically)
        if ($request->hasFile('logo_file')) {
            $data['logo'] = $this->imageService->upload(
                $request->file('logo_file'), 'stores/logos', ['width' => 300, 'format' => 'webp']
            );
        }

        if ($request->hasFile('signature_file')) {
            $data['signature'] = $this->imageService->upload(
                $request->file('signature_file'), 'stores/signatures', ['width' => 300, 'format' => 'webp']
            );
        }

        $store = Store::create($data);
        
        // Assign the creator to this store
        $store->users()->attach(auth()->id());

        // Support both traditional redirects and SPA JSON requests
        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'New store created successfully!', 'store' => $store]);
        }
        return redirect()->back()->with('success', 'New store created successfully!');
    }

    /**
     * Update store details
     */
    public function update(Request $request, Store $store)
    {
        // Authorization check
        if ($store->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'nullable|email|max:255',
            'phone'           => 'nullable|string|max:20',
            'upi_id'           => 'nullable|string',
            'gst_number'      => 'nullable|string|max:15',
            'state_id'        => 'required|exists:states,id',
            'city'            => 'nullable|string|max:100',
            'address'         => 'nullable|string',
            'zip_code'        => 'nullable|string|max:20',
            'invoice_prefix'  => 'nullable|string|max:10',
            'purchase_prefix' => 'nullable|string|max:10',
            'logo_file'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:1024',
            'signature_file'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:1024',
        ]);

        $data = $request->except(['logo_file', 'signature_file', '_token', '_method']);
        $data['is_active'] = $request->boolean('is_active', true);

        // 🌟 NEW: Replace old images intelligently to save server space
        if ($request->hasFile('logo_file')) {
            $data['logo'] = $this->imageService->upload(
                $request->file('logo_file'), 'stores/logos', 
                ['old_file' => $store->logo, 'width' => 300, 'format' => 'webp']
            );
        }

        if ($request->hasFile('signature_file')) {
            $data['signature'] = $this->imageService->upload(
                $request->file('signature_file'), 'stores/signatures', 
                ['old_file' => $store->signature, 'width' => 300, 'format' => 'webp']
            );
        }

        $store->update($data);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Store information updated!']);
        }
        return redirect()->back()->with('success', 'Store information updated!');
    }

    /**
     * Switch current active store in session.
     * Validates the store belongs to the user (not just the company)
     * and that the user has the stores.switch permission.
     */
    public function switch(Request $request)
    {
        $user = auth()->user();

        // Permission check — owners pass automatically via has_permission()
        if (! has_permission('stores.switch')) {
            abort(403, 'You do not have permission to switch stores.');
        }

        $storeId = (int) $request->store_id;

        // Validate the store belongs to this user's assigned stores
        $userStoreIds = $user->stores()->pluck('stores.id')->toArray();

        if (! in_array($storeId, $userStoreIds)) {
            abort(403, 'You are not assigned to this store.');
        }

        session(['store_id' => $storeId]);

        return back();
    }

    /**
     * Remove a store (Soft Delete)
     */
    public function destroy(Store $store)
    {
        if ($store->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        // Optional: Check if it's the only store. Usually, you want at least one.
        $store->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Store deactivated and archived.']);
        }
        return redirect()->back()->with('success', 'Store deactivated and archived.');
    }
}