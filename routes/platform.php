<?php

use App\Http\Controllers\Platform\CompanyController;
use App\Http\Controllers\Platform\CompanySubscriptionController;
use App\Http\Controllers\Platform\ContactInquiryController;
use App\Http\Controllers\Platform\ModuleController;
use App\Http\Controllers\Platform\PlanController;
use App\Http\Controllers\Platform\PlatformSeederController;
use App\Http\Controllers\Platform\SystemSettingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:super_admin'])
    ->prefix('platform')
    ->name('platform.')
    ->group(function () {

        Route::view('dashboard', 'platform.dashboard')->name('dashboard');
        Route::get('/', [ModuleController::class, 'index'])->name('dashboard');

        Route::resource('plans', PlanController::class)->except(['create', 'show', 'edit']);
        Route::resource('modules', ModuleController::class)->except(['create', 'show', 'edit']);
        Route::resource('companies', CompanyController::class);
        Route::get('companies/slug-check', [CompanyController::class, 'slugCheck'])->name('companies.slug-check');
        Route::get('companies/{company}/slug-check', [CompanyController::class, 'slugCheck'])->name('companies.slug-check.edit');

        Route::controller(CompanySubscriptionController::class)
            ->prefix('subscriptions')
            ->name('subscriptions.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/assign', 'assign')->name('assign');
            });

        Route::controller(SystemSettingController::class)
            ->prefix('system')
            ->name('system.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::put('/', 'update')->name('update');
            });

        // ── Contact Inquiries ──
        Route::controller(ContactInquiryController::class)
            ->prefix('inquiries')
            ->name('inquiries.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/{contactInquiry}', 'show')->name('show');
                Route::delete('/{contactInquiry}', 'destroy')->name('destroy');
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
