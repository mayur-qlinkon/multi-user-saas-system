<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\RazorpayWebhookController;
use App\Http\Controllers\Storefront\StorefrontPageController;
use App\Http\Controllers\Storefront\StorePublicController;
use App\Http\Controllers\StorefrontController;
use App\Http\Middleware\CheckStorefrontStatus;
use App\Http\Middleware\ResolveStorePublic;
use Illuminate\Support\Facades\Route;

// ══════════════════════════════════════════════════════════════
// 1. STORE-LEVEL ROUTES  /{company-slug}/{store-slug}/...
//    MUST be registered BEFORE the {slug} group to avoid collision
// ══════════════════════════════════════════════════════════════
Route::prefix('{slug}/{store_slug}')
    ->name('store.')
    ->middleware([ResolveStorePublic::class])
    ->group(function () {

        Route::controller(StorePublicController::class)->group(function () {
            Route::get('/',                                 'index')->name('index');
            Route::get('/category/{categorySlug}',         'category')->name('category');
            Route::get('/product/{productSlug}',           'show')->name('product');
            Route::get('/search',                          'search')->name('search');
            Route::get('/suggest',                         'suggest')->name('suggest');
            Route::post('/inquiry',                        'inquiry')->name('inquiry');
            Route::post('/analytics/section/view',         'trackView');
            Route::post('/analytics/section/{id}/click',  'trackClick');
        });

        Route::prefix('orders')->name('orders.')->controller(OrderController::class)->group(function () {
            Route::post('/',                   'storeForBranch')->name('store');
            Route::get('/{number}',            'show')->name('show');
            Route::get('/{number}/receipt',    'downloadReceipt')->name('receipt');
        });

        Route::get('/{pageSlug}', [StorefrontPageController::class, 'show'])->name('page.show');
    });

// ══════════════════════════════════════════════════════════════
// 2. COMPANY-LEVEL ROUTES  /{company-slug}/
//    Shows branch picker. Single-store → auto-redirects to store.
// ══════════════════════════════════════════════════════════════
Route::prefix('{slug}')
    ->name('storefront.')
    ->middleware([CheckStorefrontStatus::class])
    ->group(function () {

        Route::controller(StorefrontController::class)->group(function () {
            Route::get('/',    'index')->name('index');   // branch picker or redirect
            Route::post('/inquiry', 'inquiry')->name('inquiry');
            Route::post('/analytics/section/view',        'trackView')->name('analytics.section.view');
            Route::post('/analytics/section/{id}/click',  'trackClick')->name('analytics.section.click');
        });

        // Keep old URL patterns working (backward compat)
        Route::controller(StorefrontController::class)->group(function () {
            Route::get('/category/{categorySlug}', 'category')->name('category');
            Route::get('/product/{productSlug}',   'show')->name('product');
            Route::get('/search',                  'search')->name('search');
            Route::get('/suggest',                 'suggest')->name('suggest');
        });

        Route::prefix('orders')->name('orders.')->controller(OrderController::class)->group(function () {
            Route::post('/',                'store')->name('store');
            Route::get('/{number}',         'show')->name('show');
            Route::get('/{number}/receipt', 'downloadReceipt')->name('receipt');
        });

        Route::get('/{pageSlug}', [StorefrontPageController::class, 'show'])->name('page.show');
    });

Route::post('/{slug}/webhooks/razorpay', [RazorpayWebhookController::class, 'handle'])
    ->name('webhooks.razorpay');