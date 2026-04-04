<?php
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckStorefrontStatus;

use App\Http\Controllers\OrderController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\Storefront\StorefrontPageController;



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
            Route::get('/',                        'index')   ->name('index');
            Route::get('/category/{categorySlug}', 'category')->name('category');
            Route::get('/product/{productSlug}', 'show')->name('product');;
            Route::get('/search',                  'search')  ->name('search');
            Route::get('/suggest', 'suggest')->name('suggest');
        });

        Route::prefix('orders')
            ->name('orders.')
            ->controller(OrderController::class)
            ->group(function () {

                    Route::post('/',         'store')->name('store');
                    Route::get('/{number}', 'show') ->name('show');
                    Route::get('/{orderNumber}/receipt', 'downloadReceipt') ->name('receipt');                
        });        
        Route::get('/page/{pageSlug}', [StorefrontPageController::class, 'show'])
            ->name('page.show');
    
        

    });

    Route::post('/{slug}/webhooks/razorpay', [\App\Http\Controllers\RazorpayWebhookController::class, 'handle'])
    ->name('webhooks.razorpay');
    // Route::domain('{domain}') for domain users