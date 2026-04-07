<?php

use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\RazorpayTestController;
use App\Http\Middleware\MaintenanceMode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/force-logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/login');
});

/*
|--------------------------------------------------------------------------
| Public Landing Page
|--------------------------------------------------------------------------
*/
Route::middleware(MaintenanceMode::class)->group(function () {
    Route::get('/', [LandingController::class, 'index'])->name('landing');
    Route::post('/inquire', [LandingController::class, 'inquire'])->name('landing.inquire');
});

/*
|--------------------------------------------------------------------------
| Guest Routes (Unauthenticated)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    // Registration
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

    // Login
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    // Password Recovery — OTP flow
    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');
    Route::get('/forgot-password/verify', [ForgotPasswordController::class, 'showVerify'])->name('password.verify');
    Route::post('/forgot-password/verify', [ForgotPasswordController::class, 'storeVerify'])->name('password.verify.store');
});

/*
|--------------------------------------------------------------------------
| Authenticated Global Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LogoutController::class, 'destroy'])->name('logout');
});

Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
Route::redirect('/admin/help', 'https://office.qlinkon.com/help-center')->name('help');
Route::view('/db', 'tools.DB-SCHEMA')->name('db');

// Temporary Razorpay Testing Routes
Route::get('/razorpay-test', [RazorpayTestController::class, 'index']);
Route::post('/razorpay-test/verify', [RazorpayTestController::class, 'verify']);
