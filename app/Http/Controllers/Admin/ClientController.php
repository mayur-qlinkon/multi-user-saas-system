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
        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status'); // 'active' | 'inactive' | null
        $registrationType = $request->input('registration_type'); // 'registered' | 'composition' | 'unregistered' | 'overseas' | 'sez' | null

        // Non-owners only see clients linked to their assigned store(s).
        // Clients with store_id = null are company-wide and visible to all users.
        $storeIds = auth_store_ids(); // null = owner/super-admin (sees all)

        $query = Client::query()
            ->when($storeIds, fn ($q) => $q->where(function ($sq) use ($storeIds) {
                $sq->whereIn('store_id', $storeIds)
                   ->orWhereNull('store_id'); // company-wide clients visible to everyone
            }));


        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $like = '%'.$search.'%';
                $q->where('name', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('city', 'like', $like)
                    ->orWhere('gst_number', 'like', $like)
                    ->orWhere('company_name', 'like', $like);
            });
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        if (in_array($registrationType, ['registered', 'composition', 'unregistered', 'overseas', 'sez'], true)) {
            $query->where('registration_type', $registrationType);
        }

        $clients = $query->latest()->paginate(15)->withQueryString();

        // Pass states to the view for the dropdown
        $states = State::where('is_active', true)->orderBy('name')->get();

        return view('admin.clients', compact('clients', 'states', 'search', 'status', 'registrationType'));
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
