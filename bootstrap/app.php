<?php

use App\Http\Middleware\CheckModuleAccess;
use App\Http\Middleware\CheckPendingAnnouncements;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\CheckSubscription;
use App\Http\Middleware\EnsureStoreExists;
use App\Http\Middleware\EnsureValidStoreSession;
use App\Http\Middleware\IsCustomer;
use App\Http\Middleware\RoleMiddleware;
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
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            '*/webhooks/razorpay',
        ]);
        $middleware->alias([
            'permission' => CheckPermission::class,
            'role' => RoleMiddleware::class,
            'module' => CheckModuleAccess::class,
            'subscription' => CheckSubscription::class,
            'store.exists' => EnsureStoreExists::class,
            'store.session' => EnsureValidStoreSession::class,
            'announcements' => CheckPendingAnnouncements::class,
            'customer' => IsCustomer::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
