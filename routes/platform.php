<?php

use App\Http\Controllers\Platform\CompanyController;
use App\Http\Controllers\Platform\CompanySubscriptionController;
use App\Http\Controllers\Platform\ModuleController;
use App\Http\Controllers\Platform\PlanController;
use App\Http\Controllers\Platform\SystemSettingController;
use App\Http\Controllers\Platform\PlatformSeederController; 
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:super_admin'])
    ->prefix('platform')
    ->name('platform.')
    ->group(function () {

        Route::view('dashboard', 'platform.dashboard')->name('dashboard');

        Route::resource('plans',    PlanController::class)  ->except(['create', 'show', 'edit']);
        Route::resource('modules',  ModuleController::class)->except(['create', 'show', 'edit']);
        Route::resource('companies', CompanyController::class);
        Route::get('companies/slug-check', [CompanyController::class, 'slugCheck'])->name('companies.slug-check');
        Route::get('companies/{company}/slug-check', [CompanyController::class, 'slugCheck'])->name('companies.slug-check.edit');

        Route::controller(CompanySubscriptionController::class)
            ->prefix('subscriptions')
            ->name('subscriptions.')
            ->group(function () {
                Route::get('/',        'index') ->name('index');
                Route::post('/assign', 'assign')->name('assign');
            });

        Route::controller(SystemSettingController::class)
            ->prefix('system')
            ->name('system.')
            ->group(function () {
                Route::get('/',       'index') ->name('index');
                Route::put('/','update')->name('update');
            });

        // ── Visual Seeder Platform ──
        Route::controller(PlatformSeederController::class)
            ->prefix('seeders')
            ->name('seeders.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/execute', 'execute')->name('execute');
            });
            
    });

Route::view('/subscriptions', 'platform.billing.index')->name('platform.billing.index');
Route::view('/platform/settings', 'platform.settings')->name('platform.settings.index');
