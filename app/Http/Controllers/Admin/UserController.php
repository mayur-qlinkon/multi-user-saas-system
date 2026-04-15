<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Role;
use App\Models\State;
use App\Models\Store;
use App\Models\User;
use App\Services\Admin\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;

        // Added 'store_id' to pass both the search text and the store dropdown to your service
        $filters = $request->only(['search', 'status', 'store_id']);

        $users = $this->userService->getPaginatedUsers($companyId, $filters);
        $roles = Role::where('company_id', $companyId)->orWhereNull('company_id')->get();
        $stores = Store::get();

        return view('admin.users.index', compact('users', 'filters', 'roles', 'stores'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        if (! check_plan_limit('users')) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You have reached your subscription limit for users. Please upgrade your plan to add more staff.');
        }

        $companyId = Auth::user()->company_id;

        $roles = Role::where('company_id', $companyId)->orWhereNull('company_id')->get();
        $stores = Store::where('company_id', $companyId)->get();
        $states = State::orderBy('name')->get();

        return view('admin.users.create', compact('roles', 'stores', 'states'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request)
    {
        if (! check_plan_limit('users')) {
            return back()->withInput()
                ->with('error', 'User limit reached for your current plan. Please upgrade.');
        }

        try {
            $companyId = Auth::user()->company_id;

            $this->userService->createUser($companyId, $request->validated());

            return redirect()->route('admin.users.index')
                ->with('success', 'User created successfully.');

        } catch (\Exception $e) {
            // Service already logged the detailed error, we just notify the user gracefully
            return back()->withInput()->with('error', 'Failed to create user. Please try again.');
        }
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        // Security check: Ensure the user belongs to the current tenant
        if ($user->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access.');
        }

        if ($user->client()->exists()) {
            abort(404);
        }

        $user->load(['roles', 'stores', 'employee']);

        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        if ($user->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access.');
        }

        if ($user->client()->exists()) {
            abort(404);
        }

        $companyId = Auth::user()->company_id;

        // Load existing relationships so the form can auto-select them
        $user->load(['roles', 'stores']);

        $roles = Role::where('company_id', $companyId)->orWhereNull('company_id')->get();
        $stores = Store::where('company_id', $companyId)->get();
        $states = State::orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'roles', 'stores', 'states'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        if ($user->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access.');
        }

        if ($user->client()->exists()) {
            abort(404);
        }

        try {
            $data = $request->validated();

            // 🌟 THE FIX: If no boxes are checked, HTML sends nothing. We force an empty array so stores are cleared!
            if (! $request->has('store_ids')) {
                $data['store_ids'] = [];
            }

            $this->userService->updateUser($user, $data);

            return redirect()->route('admin.users.index')
                ->with('success', 'User updated successfully.');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to update user: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified user from storage.
     * Responds with JSON because deletes are usually triggered via AJAX/SweetAlert.
     */
    public function destroy(User $user)
    {
        if ($user->company_id !== Auth::user()->company_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($user->client()->exists()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Prevent users from deleting themselves
        if ($user->id === Auth::id()) {
            return response()->json(['success' => false, 'message' => 'You cannot delete your own account.'], 400);
        }

        try {
            $this->userService->deleteUser($user);

            return redirect()->route('admin.users.index')
                ->with('success', 'User deleted successfully.');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user.',
            ], 500);
        }
    }
}
