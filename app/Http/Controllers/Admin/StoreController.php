<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\State;
use App\Models\Store;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class StoreController extends Controller
{
    /**
     * Helper to determine if the current tenant has a multi-store plan.
     * If true, they can override billing/bank settings at the store level.
     */
    private function isMultiStorePlan(): bool
    {
        $subscription = tenant_subscription();
        return $subscription && $subscription->plan && $subscription->plan->store_limit > 1;
    }

    /**
     * Display a listing of the stores.
     */
    public function index()
    {
        // Tenantable trait automatically restricts this to their company_id
        $stores = Store::with('state')->latest()->paginate(15);
        $canAddMore = check_plan_limit('stores');
        
        return view('admin.stores.index', compact('stores', 'canAddMore'));
    }

    /**
     * Show the form for creating a new store.
     */
    public function create()
    {
        if (!check_plan_limit('stores')) {
            return redirect()->route('admin.stores.index')
                ->with('error', 'You have reached your subscription limit for stores. Please upgrade your plan to add more.');
        }

        $states = State::where('is_active', true)->orderBy('name')->get();
        $isMultiStore = $this->isMultiStorePlan();

        return view('admin.stores.create', compact('states', 'isMultiStore'));
    }

    /**
     * Store a newly created store in storage.
     */
    public function store(Request $request)
    {
        if (!check_plan_limit('stores')) {
            abort(403, 'Store limit reached. Please upgrade your plan.');
        }

        $isMultiStore = $this->isMultiStorePlan();

        // 1. Basic Rules (Always Applied)
        $rules = [
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['nullable', 'email', 'max:255'],
            'phone'     => ['nullable', 'string', 'max:20'],
            'address'   => ['nullable', 'string'],
            'city'      => ['nullable', 'string', 'max:100'],
            'state_id'  => ['nullable', 'exists:states,id'],
            'zip_code'  => ['nullable', 'string', 'max:20'],
            'country'   => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'logo'      => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'signature' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ];

        // 2. Billing & Override Rules (Only Applied if Multi-Store Plan)
        if ($isMultiStore) {
            $rules = array_merge($rules, [
                'gst_number'            => ['nullable', 'string', 'max:15'],
                'upi_id'                => ['nullable', 'string', 'max:255'],
                'bank_name'             => ['nullable', 'string', 'max:255'],
                'account_name'          => ['nullable', 'string', 'max:255'],
                'account_number'        => ['nullable', 'string', 'max:255'],
                'ifsc_code'             => ['nullable', 'string', 'max:255'],
                'branch_name'           => ['nullable', 'string', 'max:255'],
                'invoice_prefix'        => ['nullable', 'string', 'max:10'],
                'quotation_prefix'      => ['nullable', 'string', 'max:10'],
                'purchase_prefix'       => ['nullable', 'string', 'max:10'],
                'default_tax_type'      => ['nullable', 'string', 'max:50'],
                'default_payment_terms' => ['nullable', 'string', 'max:50'],
                'round_off_amounts'     => ['nullable', 'boolean'],
                'invoice_footer_note'   => ['nullable', 'string'],
                'invoice_terms'         => ['nullable', 'string'],
            ]);
        }

        $validated = $request->validate($rules);
        $validated['company_id'] = Auth::user()->company_id;
        $validated['is_active']  = $request->boolean('is_active', true);

        if ($isMultiStore) {
            $validated['round_off_amounts'] = $request->boolean('round_off_amounts', true);
        }

        // 🌟 IMAGE UPLOAD LOGIC (Add this right before DB::transaction)
        if ($request->hasFile('logo')) {
            // Replace with your ImageService if you have one
            $validated['logo'] = $request->file('logo')->store('stores/logos', 'public');
        }

        if ($request->hasFile('signature')) {
            $validated['signature'] = $request->file('signature')->store('stores/signatures', 'public');
        }

        try {
            DB::transaction(function () use ($validated) {
                Store::create($validated);
            });

            return redirect()->route('admin.stores.index')
                ->with('success', 'Store branch created successfully.');

        } catch (Exception $e) {
            Log::error('Error creating store: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'An error occurred while creating the store. Please check the logs and try again.');
        }
    }

    /**
     * Display the specified store details.
     */
    public function show(Store $store)
    {
        // Security checks are mostly handled by Tenantable, but extra safety is good.
        if ($store->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        $isMultiStore = $this->isMultiStorePlan();
        
        return view('admin.stores.show', compact('store', 'isMultiStore'));
    }

    /**
     * Show the form for editing the specified store.
     */
    public function edit(Store $store)
    {
        if ($store->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        $states = State::where('is_active', true)->orderBy('name')->get();
        $isMultiStore = $this->isMultiStorePlan();

        return view('admin.stores.edit', compact('store', 'states', 'isMultiStore'));
    }

    /**
     * Update the specified store in storage.
     */
    public function update(Request $request, Store $store)
    {
        if ($store->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        $isMultiStore = $this->isMultiStorePlan();

        // 1. Basic Rules
        $rules = [
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['nullable', 'email', 'max:255'],
            'phone'     => ['nullable', 'string', 'max:20'],
            'address'   => ['nullable', 'string'],
            'city'      => ['nullable', 'string', 'max:100'],
            'state_id'  => ['nullable', 'exists:states,id'],
            'zip_code'  => ['nullable', 'string', 'max:20'],
            'country'   => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'logo'      => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'signature' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ];

        // 2. Billing & Override Rules (Only if Multi-Store Plan)
        if ($isMultiStore) {
            $rules = array_merge($rules, [
                'gst_number'            => ['nullable', 'string', 'max:15'],
                'upi_id'                => ['nullable', 'string', 'max:255'],
                'bank_name'             => ['nullable', 'string', 'max:255'],
                'account_name'          => ['nullable', 'string', 'max:255'],
                'account_number'        => ['nullable', 'string', 'max:255'],
                'ifsc_code'             => ['nullable', 'string', 'max:255'],
                'branch_name'           => ['nullable', 'string', 'max:255'],
                'invoice_prefix'        => ['nullable', 'string', 'max:10'],
                'quotation_prefix'      => ['nullable', 'string', 'max:10'],
                'purchase_prefix'       => ['nullable', 'string', 'max:10'],
                'default_tax_type'      => ['nullable', 'string', 'max:50'],
                'default_payment_terms' => ['nullable', 'string', 'max:50'],
                'round_off_amounts'     => ['nullable', 'boolean'],
                'invoice_footer_note'   => ['nullable', 'string'],
                'invoice_terms'         => ['nullable', 'string'],
            ]);
        }

        $validated = $request->validate($rules);
        $validated['is_active'] = $request->boolean('is_active', false);

        if ($isMultiStore) {
            $validated['round_off_amounts'] = $request->boolean('round_off_amounts', false);
        }

        // 🌟 IMAGE UPLOAD LOGIC FOR UPDATE (Add this right before DB::transaction)
        if ($request->hasFile('logo')) {
            if ($store->logo) {
                Storage::disk('public')->delete($store->logo);
            }
            $validated['logo'] = $request->file('logo')->store('stores/logos', 'public');
        }

        if ($request->hasFile('signature')) {
            if ($store->signature) {
                Storage::disk('public')->delete($store->signature);
            }
            $validated['signature'] = $request->file('signature')->store('stores/signatures', 'public');
        }

        try {
            DB::transaction(function () use ($validated, $store) {
                $store->update($validated);
            });

            return redirect()->route('admin.stores.index')
                ->with('success', 'Store branch updated successfully.');

        } catch (Exception $e) {
            Log::error('Error updating store: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'An error occurred while updating the store. Please try again.');
        }
    }
    /**
     * Switch current active store in session.
     * Validates the store belongs to the user (not just the company)
     * and that the user has the stores.switch permission.
     */
    public function switch(Request $request)
    {
        $user = Auth::user();

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
     * Remove the specified store from storage.
     */
    public function destroy(Store $store)
    {
        if ($store->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        // Optional safety net: Prevent deleting if it's their only active store
        $totalStores = Store::where('company_id', Auth::user()->company_id)->count();
        if ($totalStores <= 1) {
            return back()->with('error', 'You cannot delete your primary store. You must have at least one store active.');
        }

        try {
            // Note: If you have foreign key constraints (like invoices linked to a store), 
            // you might want to soft-delete or handle that gracefully here.
            $store->delete();
            return redirect()->route('admin.stores.index')->with('success', 'Store branch deleted successfully.');
            
        } catch (Exception $e) {
            Log::error('Error deleting store: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete store. It may be linked to existing records.');
        }
    }
}