<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClientRequest;
use App\Http\Requests\Admin\UpdateClientRequest;
use App\Models\Client;
use App\Models\State;   // 🌟 IMPORTED
use Illuminate\Http\Request;  // 🌟 IMPORTED

class ClientController extends Controller
{
    public function index(Request $request)
    {
        // Tenantable trait automatically restricts this to the owner's company!
        $query = Client::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        $clients = $query->latest()->paginate(15);

        // Pass states to the view for the dropdown
        $states = State::where('is_active', true)->orderBy('name')->get();

        return view('admin.clients', compact('clients', 'states'));
    }

    /**
     * 🌟 Uses StoreClientRequest for strict Company-level validation
     */
    public function store(StoreClientRequest $request)
    {
        // Retrieve the safely validated data
        $validated = $request->validated();

        // Handle the store-only scope logic
        $validated['store_id'] = $request->boolean('store_only') ? session('store_id') : null;

        $client = Client::create($validated);

        // Handle AJAX/API responses (e.g., from an Invoice Create Modal)
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Client added successfully',
                'client' => $client,
            ]);
        }

        return redirect()->route('admin.clients.index')->with('success', 'Client created successfully!');
    }

    /**
     * 🌟 Uses UpdateClientRequest for strict validation (ignores own phone ID)
     */
    public function update(UpdateClientRequest $request, Client $client)
    {
        // Retrieve the safely validated data
        $validated = $request->validated();

        // Handle the store-only scope logic
        $validated['store_id'] = $request->boolean('store_only') ? session('store_id') : null;

        $client->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Client updated successfully!']);
        }

        return redirect()->route('admin.clients.index')->with('success', 'Client updated successfully!');
    }

    public function ajaxSearch(Request $request)
    {
        // Tenantable restricts this automatically!
        $query = Client::query();

        if ($request->filled('term')) {
            $term = $request->term;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('company_name', 'like', "%{$term}%");
            });
        }

        $clients = $query->latest()->take($request->input('limit', 15))->get();

        $results = $clients->map(function ($client) {
            return [
                'id' => $client->id,
                'name' => $client->name,
                'phone' => $client->phone,
                'email' => $client->email,
                'company_name' => $client->company_name,
                'city' => $client->city,
                'display_text' => $client->name.($client->phone ? " ({$client->phone})" : ''),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()->route('admin.clients.index')->with('success', 'Client deleted successfully.');
    }
}
