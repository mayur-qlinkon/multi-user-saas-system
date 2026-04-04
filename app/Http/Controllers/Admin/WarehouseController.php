<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\Store;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;


class WarehouseController extends Controller
{
    public function index()
    {
        // Fetch all warehouses for this company, including the store name
        $warehouses = Warehouse::with('store')->latest()->get();
        
        // Fetch active stores so the user can select which store this warehouse belongs to in the modal
        $stores = Store::where('is_active', true)->get();

        $states = State::where('is_active', true)->orderBy('name')->get();

        return view('admin.warehouses', compact('warehouses', 'stores','states'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_id'       => ['required', Rule::exists('stores', 'id')->where('company_id', Auth::user()->company_id)],
            'name'           => ['required', 'string', 'max:255'],
            'code'           => ['nullable', 'string', 'max:50'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'phone'          => ['nullable', 'string', 'max:20'],
            'email'          => ['nullable', 'email', 'max:100'],
            'address'        => ['nullable', 'string'],
            'city'           => ['nullable', 'string', 'max:100'],
            'state_id'       => ['nullable', 'exists:states,id'],
            'zip_code'       => ['nullable', 'string', 'max:20'],
            'is_default'     => ['boolean'],
            'is_active'      => ['boolean'],
        ]);

        $validated['is_default'] = $request->boolean('is_default', false);
        $validated['is_active']  = $request->boolean('is_active', true);

        // LOGIC: If this is set as default, remove default status from all other warehouses in this store
        if ($validated['is_default']) {
            Warehouse::where('store_id', $validated['store_id'])->update(['is_default' => false]);
        }

        Warehouse::create($validated);

        return back()->with('success', 'Warehouse created successfully.');
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'store_id'       => ['required', Rule::exists('stores', 'id')->where('company_id', Auth::user()->company_id)],
            'name'           => ['required', 'string', 'max:255'],
            'code'           => ['nullable', 'string', 'max:50'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'phone'          => ['nullable', 'string', 'max:20'],
            'email'          => ['nullable', 'email', 'max:100'],
            'address'        => ['nullable', 'string'],
            'city'           => ['nullable', 'string', 'max:100'],
            'state_id'       => ['nullable', 'exists:states,id'],
            'zip_code'       => ['nullable', 'string', 'max:20'],
            'is_default'     => ['boolean'],
            'is_active'      => ['boolean'],
        ]);

        $validated['is_default'] = $request->boolean('is_default', false);
        $validated['is_active']  = $request->boolean('is_active', false);

        if ($validated['is_default']) {
            Warehouse::where('store_id', $validated['store_id'])
                     ->where('id', '!=', $warehouse->id)
                     ->update(['is_default' => false]);
        }

        $warehouse->update($validated);

        return back()->with('success', 'Warehouse updated successfully.');
    }

    public function destroy(Warehouse $warehouse)
    {
        if ($warehouse->is_default) {
            return back()->with('error', 'You cannot delete the default warehouse. Please set another warehouse as default first.');
        }

        $warehouse->delete();
        
        return back()->with('success', 'Warehouse deleted successfully.');
    }
}