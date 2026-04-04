<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
                Route::middleware('web')
                    ->group(base_path('routes/admin.php'));

                Route::middleware('web')
                    ->group(base_path('routes/platform.php'));

                Route::middleware('web')
                    ->group(base_path('routes/storefront.php'));

                Route::middleware('web')
                    ->group(base_path('routes/customer.php'));
            }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            '*/webhooks/razorpay',
        ]);
        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'module' => \App\Http\Middleware\CheckModuleAccess::class,
            'subscription' => \App\Http\Middleware\CheckSubscription::class,
            'store.exists' => \App\Http\Middleware\EnsureStoreExists::class,
            'store.session' => \App\Http\Middleware\EnsureValidStoreSession::class,
            'announcements' => \App\Http\Middleware\CheckPendingAnnouncements::class,
        ]);
    })   
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
