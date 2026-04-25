<?php

use App\Http\Controllers\Auth\Customer\CustomerAuthController;
use App\Http\Controllers\Customer\CustomerPortalController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\Storefront\StorefrontPageController;
use App\Http\Middleware\CheckStorefrontStatus;
use App\Http\Middleware\ResolveCustomDomain;
use Illuminate\Support\Facades\Route;

// All routes here are protected by ResolveCustomDomain, which aborts(404)
// if the host is not a registered custom domain. This means these routes
// are effectively invisible to your main domain.

Route::middleware([ResolveCustomDomain::class, CheckStorefrontStatus::class])
    ->group(function () {

        // ── Public storefront ──
        Route::controller(StorefrontController::class)->group(function () {
            Route::get('/', 'index')->name('custom_domain.storefront.index');
            Route::get('/category/{categorySlug}', 'category');
            Route::get('/product/{productSlug}', 'show');
            Route::get('/search', 'search');
            Route::get('/suggest', 'suggest');
            Route::post('/analytics/section/view', 'trackView');
            Route::post('/analytics/section/{id}/click', 'trackClick');
            Route::post('/inquiry', 'inquiry');
        });

        // Custom pages
        Route::get('/page/{pageSlug}', [StorefrontPageController::class, 'show']);

        // Orders
        Route::prefix('orders')->controller(OrderController::class)->group(function () {
            Route::post('/', 'store');
            Route::get('/{number}', 'show');
            Route::get('/{orderNumber}/receipt', 'downloadReceipt');
        });

        // ── Customer auth (on the custom domain) ──
        Route::middleware('guest')->group(function () {
            Route::get('/login', [CustomerAuthController::class, 'showLoginForm'])
                ->name('custom_domain.storefront.login');
            Route::post('/login', [CustomerAuthController::class, 'login']);
            Route::get('/register', [CustomerAuthController::class, 'showRegisterForm']);
            Route::post('/register', [CustomerAuthController::class, 'register']);
        });

        // ── Customer portal ──
        Route::middleware(['auth', 'customer'])
            ->prefix('portal')
            ->group(function () {
                Route::get('/dashboard', [CustomerPortalController::class, 'index']);
                Route::get('/orders', [CustomerPortalController::class, 'orders']);
                Route::post('/logout', [CustomerAuthController::class, 'logout']);
                Route::get('/addresses', [CustomerPortalController::class, 'addresses']);
                Route::post('/addresses', [CustomerPortalController::class, 'storeAddress']);
                Route::get('/profile', [CustomerPortalController::class, 'profile']);
                Route::post('/profile', [CustomerPortalController::class, 'updateProfile']);
            });

    });

// NOTE: Admin routes (/admin/*) already work on custom domains automatically!
// They are in admin.php with prefix 'admin' and NO domain restriction.
// clientdomain.com/admin → works ✅