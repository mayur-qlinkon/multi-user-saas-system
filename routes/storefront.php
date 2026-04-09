<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\RazorpayWebhookController;
use App\Http\Controllers\Storefront\StorefrontPageController;
use App\Http\Controllers\StorefrontController;
use App\Http\Middleware\CheckStorefrontStatus;
use Illuminate\Support\Facades\Route;

// routes/storefront.php

// Route::name('storefront.')
//     ->group(function () {

//         Route::controller(StorefrontController::class)->group(function () {
//             Route::get('/', 'index')->name('index');
//             Route::get('/category/{categorySlug}', 'category')->name('category');
//             Route::get('/product/{productSlug}', 'show')->name('product');
//             Route::get('/search', 'search')->name('search');
//             Route::get('/suggest', 'suggest')->name('suggest');
//         });

//         Route::prefix('orders')
//             ->name('orders.')
//             ->controller(OrderController::class)
//             ->group(function () {
//                 Route::post('/', 'store')->name('store');
//                 Route::get('/{number}', 'show')->name('show');
//                 Route::get('/{orderNumber}/receipt', 'downloadReceipt')->name('receipt');
//             });

//     });

Route::prefix('{slug}')
    ->name('storefront.')
    ->middleware([CheckStorefrontStatus::class])
    ->group(function () {

        Route::controller(StorefrontController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/category/{categorySlug}', 'category')->name('category');
            Route::get('/product/{productSlug}', 'show')->name('product');
            Route::get('/search', 'search')->name('search');
            Route::get('/suggest', 'suggest')->name('suggest');
            Route::post('/analytics/section/view', 'trackView')->name('analytics.section.view');
            Route::post('/analytics/section/{id}/click', 'trackClick')->name('analytics.section.click');
            Route::post('/inquiry', 'inquiry')->name('inquiry');
        });

        Route::prefix('orders')
            ->name('orders.')
            ->controller(OrderController::class)
            ->group(function () {

                Route::post('/', 'store')->name('store');
                Route::get('/{number}', 'show')->name('show');
                Route::get('/{orderNumber}/receipt', 'downloadReceipt')->name('receipt');
            });
        Route::get('/{pageSlug}', [StorefrontPageController::class, 'show'])
            ->name('page.show');

    });

Route::post('/{slug}/webhooks/razorpay', [RazorpayWebhookController::class, 'handle'])
    ->name('webhooks.razorpay');
// Route::domain('{domain}') for domain users
