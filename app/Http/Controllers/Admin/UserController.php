<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        // 1. Controller extracts the context
        $isOwner = is_owner();
        $requestedStoreId = $request->input('store_id');
        $activeSessionStoreId = session('store_id');

        // 2. Controller asks the Service for exactly what it needs using raw data
        $users  = $this->userService->getFilteredUsers($isOwner, $requestedStoreId, $activeSessionStoreId);
        $stores = $this->userService->getTenantStores();
        $roles  = $this->userService->getAvailableRoles();

        // 3. Controller passes the variables to the view
        return view('admin.users', compact('users', 'stores', 'roles'));
    }

    public function store(StoreUserRequest $request)
    {
        // 🚨 Check the limit before proceeding!
        if (!check_plan_limit('users')) {
            return back()->with('error', 'You have reached your plan\'s User limit. Please upgrade your subscription to add more staff.');
        }
        $this->userService->storeUser($request->validated());

        return redirect()->route('admin.users.index')
                         ->with('success', 'Staff member added successfully.');
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->userService->updateUser($user, $request->validated());

        return redirect()->route('admin.users.index')
                         ->with('success', 'Staff member updated successfully.');
    }

    public function destroy(User $user)
    {
        // Optional: Prevent the owner from deleting themselves!
        if (Auth::id() === $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $this->userService->deleteUser($user);

        return back()->with('success', 'Staff member removed.');
    }
}