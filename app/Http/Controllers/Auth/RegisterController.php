<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function __construct(private AuthService $authService) {}

    // -------------------------------------------------------
    // GET /register — Show registration form
    // -------------------------------------------------------
    public function create(): View
    {
        return view('auth.register');
    }

    // -------------------------------------------------------
    // POST /register — Handle form submission
    // -------------------------------------------------------
    public function store(RegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $imagePath = null;
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')
                ->store('profile-images', 'public');
        }

        $user = $this->authService->register($data);

        return redirect()
            ->route('login')          // change to your post-register route
            ->with('success', 'Welcome! Your account has been created.');
    }
}
