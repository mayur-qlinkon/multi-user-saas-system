<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\Store;
use App\Models\State; // 🌟 ADDED: For the state selector
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    /**
     * Display a listing of the suppliers.
     */
    public function index()
    {
        // Eager load the store and state relationships
        $suppliers = Supplier::with(['store', 'state'])->orderBy('created_at', 'desc')->get();
        
        // Fetch active stores for the logged-in owner to display in the modal dropdown
        $stores = Store::where('is_active', true)->get();

        // 🌟 NEW: Fetch states for the GST logic dropdown
        $states = State::where('is_active', true)->orderBy('name')->get();

        return view('admin.suppliers', compact('suppliers', 'stores', 'states'));
    }

    /**
     * Store a newly created supplier.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_id'          => [
                'nullable', 
                Rule::exists('stores', 'id')->where('company_id', Auth::user()->company_id)
            ],
            'name'              => 'required|string|max:150',
            'email'             => 'nullable|email|max:100',
            'phone'             => 'nullable|string|max:20',
            'address'           => 'nullable|string',
            'city'              => 'nullable|string|max:100',
            'pincode'           => 'nullable|string|max:10',
            'state_id'          => 'required|exists:states,id', // 🌟 REQUIRED for GST routing
            
            // Indian Compliance
            'gstin'             => 'nullable|string|max:15',
            'pan'               => 'nullable|string|max:10',
            'registration_type' => 'required|string|in:regular,composition,unregistered,sez,overseas',
            
            // Banking
            'bank_name'         => 'nullable|string|max:255',
            'account_number'    => 'nullable|string|max:255',
            'ifsc_code'         => 'nullable|string|max:50',
            'branch'            => 'nullable|string|max:255',
            
            // Financials
            'opening_balance'   => 'nullable|numeric|min:0',
            'balance_type'      => 'required|in:payable,advance',
            'credit_days'       => 'nullable|integer|min:0',
            'credit_limit'      => 'nullable|numeric|min:0',
            
            'is_active'         => 'boolean',
            'notes'            => 'nullable|string',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        
        // 🌟 Set initial current balance
        $validated['current_balance'] = $validated['opening_balance'] ?? 0;

        $supplier = Supplier::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Supplier onboarded successfully!', 'data' => $supplier]);
        }
        return redirect()->back()->with('success', 'Supplier onboarded successfully!');
    }

    /**
     * Update the specified supplier.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'store_id'          => [
                'nullable', 
                Rule::exists('stores', 'id')->where('company_id', Auth::user()->company_id)
            ],
            'name'              => 'required|string|max:150',
            'email'             => 'nullable|email|max:100',
            'phone'             => 'nullable|string|max:20',
            'address'           => 'nullable|string',
            'city'              => 'nullable|string|max:100',
            'pincode'           => 'nullable|string|max:10',
            'state_id'          => 'required|exists:states,id',
            
            // Indian Compliance
            'gstin'             => 'nullable|string|max:15',
            'pan'               => 'nullable|string|max:10',
            'registration_type' => 'required|string|in:regular,composition,unregistered,sez,overseas',
            
            // Banking
            'bank_name'         => 'nullable|string|max:255',
            'account_number'    => 'nullable|string|max:255',
            'ifsc_code'         => 'nullable|string|max:50',
            'branch'            => 'nullable|string|max:255',
            
            // Financials
            'opening_balance'   => 'nullable|numeric|min:0',
            'balance_type'      => 'required|in:payable,advance',
            'credit_days'       => 'nullable|integer|min:0',
            'credit_limit'      => 'nullable|numeric|min:0',
            
            'is_active'         => 'boolean',
            'notes'            => 'nullable|string',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        // Note: In a live ERP, updating opening balance after transactions exist requires recalculating the ledger.
        // For now, we update it as provided. If opening balance changed, we adjust current balance roughly.
        if (isset($validated['opening_balance']) && $supplier->opening_balance != $validated['opening_balance']) {
            $difference = $validated['opening_balance'] - $supplier->opening_balance;
            $validated['current_balance'] = $supplier->current_balance + $difference;
        }

        $supplier->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Supplier details updated!']);
        }
        return redirect()->back()->with('success', 'Supplier details updated!');
    }

    /**
     * Remove the specified supplier (Soft Delete).
     */
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        
        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Supplier removed from active list.']);
        }
        return redirect()->back()->with('success', 'Supplier removed from active list.');
    }
}