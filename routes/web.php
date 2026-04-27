<?php

use App\Http\Controllers\Auth\GuardLoginController;
use App\Http\Controllers\Auth\OAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('auth')->group(function () {
    Route::get('/line', [OAuthController::class, 'redirectToLine'])->name('auth.line');
    Route::get('/line/callback', [OAuthController::class, 'handleLineCallback'])->name('auth.line.callback');

    Route::get('/google', [OAuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/google/callback', [OAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

Route::view('/admin/login', 'auth.admin-login')->middleware('guest:admin')->name('admin.login');

Route::middleware('guest:staff')->group(function () {
    Route::get('/staff/login', [GuardLoginController::class, 'show'])->defaults('guard', 'staff')->name('staff.login');
    Route::post('/staff/login', [GuardLoginController::class, 'store'])->defaults('guard', 'staff')->name('staff.login.store');
});

Route::middleware('guest:portal')->group(function () {
    Route::get('/portal/login', [GuardLoginController::class, 'show'])->defaults('guard', 'portal')->name('portal.login');
    Route::post('/portal/login', [GuardLoginController::class, 'store'])->defaults('guard', 'portal')->name('portal.login.store');
});

Route::post('/staff/logout', [GuardLoginController::class, 'destroy'])->defaults('guard', 'staff')->middleware('auth:staff')->name('staff.logout');
Route::post('/portal/logout', [GuardLoginController::class, 'destroy'])->defaults('guard', 'portal')->middleware('auth:portal')->name('portal.logout');
Route::post('/admin/logout', [GuardLoginController::class, 'destroy'])->defaults('guard', 'admin')->middleware('auth:admin')->name('admin.logout');
Route::post('/user/logout', [GuardLoginController::class, 'destroy'])->defaults('guard', 'user')->middleware('auth:user')->name('user.logout');

Route::get('/admin/dashboard', fn () => view('dashboard'))->middleware('auth:admin')->name('admin.dashboard');
Route::get('/staff/dashboard', fn () => view('dashboard'))->middleware('auth:staff')->name('staff.dashboard');
Route::get('/portal/dashboard', fn () => view('dashboard'))->middleware('auth:portal')->name('portal.dashboard');

Route::middleware('auth:user')->group(function () {
    Route::get('/user/hub', fn () => view('user.hub'))->name('user.hub');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
