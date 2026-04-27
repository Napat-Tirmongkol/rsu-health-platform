<?php

use App\Http\Controllers\Auth\GuardLoginController;
use App\Http\Controllers\Auth\OAuthController;
use App\Http\Controllers\User\HubController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

if (app()->isLocal()) {
    Route::get('/test-user-login', function () {
        $user = User::where('email', 'patient@example.com')->first();

        if (! $user) {
            abort(404, 'Test user not found. Run php artisan migrate:fresh --seed first.');
        }

        Auth::guard('user')->login($user);

        return redirect()->route('user.hub');
    });
}

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

Route::get('/admin/dashboard', fn () => view('admin.dashboard'))->middleware('auth:admin')->name('admin.dashboard');
Route::get('/admin/campaigns', fn () => view('admin.campaigns'))->middleware('auth:admin')->name('admin.campaigns');
Route::get('/admin/bookings', fn () => view('admin.bookings'))->middleware('auth:admin')->name('admin.bookings');
Route::get('/admin/time-slots', fn () => view('admin.time_slots'))->middleware('auth:admin')->name('admin.time_slots');
Route::get('/admin/manage-staff', fn () => view('admin.manage_staff'))->middleware('auth:admin')->name('admin.manage_staff');
Route::get('/admin/activity-logs', fn () => view('admin.activity_logs'))->middleware('auth:admin')->name('admin.activity_logs');
Route::get('/admin/reports', fn () => view('admin.reports'))->middleware('auth:admin')->name('admin.reports');
Route::get('/admin/users', fn () => view('admin.users'))->middleware('auth:admin')->name('admin.users');
Route::get('/staff/dashboard', fn () => view('dashboard'))->middleware('auth:staff')->name('staff.dashboard');
Route::get('/portal/dashboard', fn () => view('dashboard'))->middleware('auth:portal')->name('portal.dashboard');

Route::middleware('auth:user')->group(function () {
    Route::get('/user/hub', [HubController::class, 'index'])->name('user.hub');
    Route::get('/user/booking', fn () => view('user.booking'))->name('user.booking');
    Route::get('/user/history', fn () => view('user.history'))->name('user.history');
    Route::get('/user/chat', fn () => view('user.chat'))->name('user.chat');
    Route::get('/user/profile', fn () => view('user.profile'))->name('user.profile');
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
