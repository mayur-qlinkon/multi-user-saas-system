<?php

use App\Http\Controllers\Auth\Customer\CustomerAuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Customer\CustomerPortalController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\RazorpayTestController;
use App\Http\Middleware\MaintenanceMode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ══════════════════════════════════════════════════════════════
// CRITICAL: Restrict ALL main-app routes to your primary domain.
// This frees up "/" on custom domains for the storefront.
// ══════════════════════════════════════════════════════════════
$appDomain = parse_url(config('app.url'), PHP_URL_HOST);

Route::domain($appDomain)->group(function () {

Route::get('/force-logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/login');
});

/*
|--------------------------------------------------------------------------
| Public Landing Page
|--------------------------------------------------------------------------
*/
Route::middleware(MaintenanceMode::class)->group(function () {
    Route::get('/', [LandingController::class, 'index'])->name('landing');
    Route::post('/inquire', [LandingController::class, 'inquire'])->name('landing.inquire');
});

/*
|--------------------------------------------------------------------------
| Guest Routes (Unauthenticated)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    // Registration
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

    // Login
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    // Password Recovery — OTP flow
    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');
    Route::get('/forgot-password/verify', [ForgotPasswordController::class, 'showVerify'])->name('password.verify');
    Route::post('/forgot-password/verify', [ForgotPasswordController::class, 'storeVerify'])->name('password.verify.store');
});

/*
|--------------------------------------------------------------------------
| Authenticated Global Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LogoutController::class, 'destroy'])->name('logout');
});

Route::view('/subscriptions', 'admin.subscriptions')->name('subscriptions.index');
Route::redirect('/admin/help', 'https://office.qlinkon.com/help-center')->name('help');
Route::view('/db', 'tools.DB-SCHEMA')->name('db');

// Temporary Razorpay Testing Routes
Route::get('/razorpay-test', [RazorpayTestController::class, 'index']);
Route::post('/razorpay-test/verify', [RazorpayTestController::class, 'verify']);

/*
|--------------------------------------------------------------------------
| Customer Storefront Routes
|--------------------------------------------------------------------------
| These routes are loaded by RouteServiceProvider/bootstrap app.php
| and should be prefixed with '/{slug}/portal' or similar.
*/

    Route::prefix('{slug}')->name('storefront.')->group(function () {

        // ── GUEST ROUTES (Login & Register) ──
        Route::middleware('guest')->group(function () {
            Route::get('/login', [CustomerAuthController::class, 'showLoginForm'])->name('login');
            Route::post('/login', [CustomerAuthController::class, 'login'])->name('login.submit');

            Route::get('/register', [CustomerAuthController::class, 'showRegisterForm'])->name('register');
            Route::post('/register', [CustomerAuthController::class, 'register'])->name('register.submit');
        });

        // ── PROTECTED CUSTOMER ROUTES ──
        // We will create a 'customer' middleware to ensure Super Admins/Staff don't get stuck here
        Route::middleware(['auth', 'customer'])->prefix('portal')->name('portal.')->group(function () {

            Route::get('/dashboard', [CustomerPortalController::class, 'index'])->name('dashboard');
            Route::get('/orders', [CustomerPortalController::class, 'orders'])->name('orders');
            Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');

            // Addresses Management
            Route::get('/addresses', [CustomerPortalController::class, 'addresses'])->name('addresses');
            Route::post('/addresses', [CustomerPortalController::class, 'storeAddress'])->name('addresses.store');

            // Profile Management
            Route::get('/profile', [CustomerPortalController::class, 'profile'])->name('profile');
            Route::post('/profile', [CustomerPortalController::class, 'updateProfile'])->name('profile.update');

        });
    });
});