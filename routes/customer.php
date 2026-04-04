<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\Customer\CustomerAuthController;
use App\Http\Controllers\Customer\CustomerPortalController;

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
    Route::middleware(['auth', 'role:customer'])->prefix('portal')->name('portal.')->group(function () {
        
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