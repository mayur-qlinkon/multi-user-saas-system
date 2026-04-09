<?php

use App\Http\Controllers\Platform\CompanyController;
use App\Http\Controllers\Platform\CompanySubscriptionController;
use App\Http\Controllers\Platform\ContactInquiryController;
use App\Http\Controllers\Platform\EmailTemplateController;
use App\Http\Controllers\Platform\ModuleController;
use App\Http\Controllers\Platform\PermissionController;
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

        // ── Email Templates ──
        Route::resource('email-templates', EmailTemplateController::class)->except(['create', 'show', 'edit']);

        // ── Visual Seeder Platform ──
        Route::controller(PlatformSeederController::class)
            ->prefix('seeders')
            ->name('seeders.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/execute', 'execute')->name('execute');
            });

        // ── SYSTEM PERMISSIONS ──

        // Sync Defaults (Must be defined before routes with {parameters})
        Route::post('permissions/sync', [PermissionController::class, 'syncDefault'])->name('permissions.sync');

        // Standard CRUD (Single-page modal setup)
        Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
        Route::post('permissions', [PermissionController::class, 'store'])->name('permissions.store');
        Route::put('permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update');
        Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');

    });
