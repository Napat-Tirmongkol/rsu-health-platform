<?php

use App\Http\Controllers\Admin\BorrowReceiptController;
use App\Http\Controllers\Auth\GuardLoginController;
use App\Http\Controllers\Auth\OAuthController;
use App\Http\Controllers\Staff\IdentityScanController;
use App\Http\Controllers\User\BorrowController;
use App\Http\Controllers\User\HubController;
use App\Http\Controllers\User\ServiceController;
use App\Http\Controllers\Portal\PortalDashboardController;
use App\Http\Controllers\Portal\PortalChatbotController;
use App\Models\User;
use App\Models\Portal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

if (app()->environment(['local', 'testing'])) {
    Route::get('/test-user-login', function () {
        $user = User::where('email', 'patient@example.com')->first();

        if (! $user) {
            abort(404, 'Test user not found. Run php artisan migrate:fresh --seed first.');
        }

        Auth::guard('user')->login($user);

        return redirect()->route('user.hub');
    });

    Route::get('/dev-login/portal', function () {
        $portal = Portal::firstOrCreate(
            ['email' => 'portal@test.com'],
            [
                'name' => 'Developer Portal',
                'password' => Hash::make('password123'),
            ]
        );

        Auth::guard('portal')->login($portal);

        session(['portal_id' => $portal->id]);

        return redirect()->route('portal.dashboard');
    })->name('dev.login.portal');
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

Route::get('/portal', function () {
    return Auth::guard('portal')->check()
        ? redirect()->route('portal.dashboard')
        : redirect()->route('portal.login');
})->name('portal');

Route::middleware('guest:portal')->group(function () {
    Route::get('/portal/login', [GuardLoginController::class, 'show'])->defaults('guard', 'portal')->name('portal.login');
    Route::post('/portal/login', [GuardLoginController::class, 'store'])->defaults('guard', 'portal')->name('portal.login.store');
});

Route::post('/staff/logout', [GuardLoginController::class, 'destroy'])->defaults('guard', 'staff')->middleware('auth:staff')->name('staff.logout');
Route::post('/portal/logout', [GuardLoginController::class, 'destroy'])->defaults('guard', 'portal')->middleware('auth:portal')->name('portal.logout');
Route::post('/admin/logout', [GuardLoginController::class, 'destroy'])->defaults('guard', 'admin')->middleware('auth:admin')->name('admin.logout');
Route::post('/user/logout', [GuardLoginController::class, 'destroy'])->defaults('guard', 'user')->middleware('auth:user')->name('user.logout');

Route::get('/admin/dashboard', fn () => view('admin.dashboard'))->middleware('auth:admin')->name('admin.dashboard');
Route::get('/admin/workspaces/campaign', fn () => view('admin.workspaces.campaign'))->middleware(['auth:admin', 'admin.module:campaign'])->name('admin.workspace.campaign');
Route::get('/admin/workspaces/borrow', fn () => view('admin.workspaces.borrow'))->middleware(['auth:admin', 'admin.module:borrow'])->name('admin.workspace.borrow');
Route::get('/admin/campaigns', fn () => view('admin.campaigns'))->middleware(['auth:admin', 'admin.module:campaign', 'admin.action:campaign.manage'])->name('admin.campaigns');
Route::get('/admin/bookings', fn () => view('admin.bookings'))->middleware(['auth:admin', 'admin.module:campaign', 'admin.action:campaign.booking.manage'])->name('admin.bookings');
Route::get('/admin/borrow-requests', fn () => view('admin.borrow_requests'))->middleware(['auth:admin', 'admin.module:borrow', 'admin.action:borrow.request.approve'])->name('admin.borrow_requests');
Route::get('/admin/inventory', fn () => view('admin.inventory'))->middleware(['auth:admin', 'admin.module:borrow', 'admin.action:borrow.inventory.manage'])->name('admin.inventory');
Route::get('/admin/borrow-returns', fn () => view('admin.borrow_returns'))->middleware(['auth:admin', 'admin.module:borrow', 'admin.action:borrow.return.process'])->name('admin.borrow_returns');
Route::get('/admin/borrow-fines', fn () => view('admin.borrow_fines'))->middleware(['auth:admin', 'admin.module:borrow', 'admin.action:borrow.fine.collect'])->name('admin.borrow_fines');
Route::get('/admin/walk-in-borrow', fn () => view('admin.walk_in_borrow'))->middleware(['auth:admin', 'admin.module:borrow', 'admin.action:borrow.inventory.manage'])->name('admin.walk_in_borrow');
Route::get('/admin/borrow-payments/{payment}/receipt', [BorrowReceiptController::class, 'show'])->middleware(['auth:admin', 'admin.module:borrow', 'admin.action:borrow.fine.collect'])->name('admin.borrow_payments.receipt');
Route::get('/admin/time-slots', fn () => view('admin.time_slots'))->middleware(['auth:admin', 'admin.module:campaign', 'admin.action:campaign.manage'])->name('admin.time_slots');
Route::get('/admin/manage-staff', fn () => view('admin.manage_staff'))->middleware('auth:admin')->name('admin.manage_staff');
Route::get('/admin/system-admins', fn () => view('admin.system_admins'))->middleware(['auth:admin', 'admin.platform'])->name('admin.system_admins');
Route::get('/admin/system-settings', fn () => view('admin.system_settings'))->middleware(['auth:admin', 'admin.platform'])->name('admin.system_settings');
Route::get('/admin/activity-logs', fn () => view('admin.activity_logs'))->middleware('auth:admin')->name('admin.activity_logs');
Route::get('/admin/reports', fn () => view('admin.reports'))->middleware(['auth:admin', 'admin.module:campaign', 'admin.action:campaign.manage'])->name('admin.reports');
Route::get('/admin/users', fn () => view('admin.users'))->middleware(['auth:admin', 'admin.module:campaign', 'admin.action:campaign.manage'])->name('admin.users');
Route::get('/dev-login', function () {
    $admin = \App\Models\Admin::firstOrCreate(
        ['email' => 'admin@test.com'],
        [
            'name' => 'Developer Admin',
            'password' => \Hash::make('password123'),
            'clinic_id' => 1,
            'module_permissions' => ['campaign', 'borrow'],
            'default_workspace' => 'campaign',
        ]
    );
    Auth::guard('admin')->login($admin);
    return redirect()->route($admin->landingRouteName());
})->name('dev.login');

Route::get('/staff/dashboard', fn () => view('dashboard'))->middleware('auth:staff')->name('staff.dashboard');
Route::get('/staff/scan', [IdentityScanController::class, 'show'])->middleware('auth:staff')->name('staff.scan');
Route::get('/staff/scan/campaign/{campaign}', [IdentityScanController::class, 'show'])->middleware('auth:staff')->name('staff.scan.campaign');
Route::post('/staff/scan/verify', [IdentityScanController::class, 'verify'])->middleware('auth:staff')->name('staff.scan.verify');
Route::post('/staff/scan/check-in', [IdentityScanController::class, 'checkIn'])->middleware('auth:staff')->name('staff.scan.check-in');
Route::middleware('auth:portal')->prefix('portal')->name('portal.')->group(function () {
    Route::get('/dashboard', [PortalDashboardController::class, 'index'])->name('dashboard');
    Route::get('/clinics', [PortalDashboardController::class, 'clinics'])->name('clinics');
    Route::get('/settings', [PortalDashboardController::class, 'settings'])->name('settings');
    Route::get('/chatbot/faqs', [PortalChatbotController::class, 'faqs'])->name('chatbot.faqs');
    Route::post('/chatbot/faqs', [PortalChatbotController::class, 'storeFaq'])->name('chatbot.faqs.store');
    Route::put('/chatbot/faqs/{faqId}', [PortalChatbotController::class, 'updateFaq'])->name('chatbot.faqs.update');
    Route::get('/chatbot/settings', [PortalChatbotController::class, 'settings'])->name('chatbot.settings');
    Route::post('/chatbot/settings', [PortalChatbotController::class, 'updateSettings'])->name('chatbot.settings.update');
});

Route::middleware('auth:user')->group(function () {
    Route::get('/user/hub', [HubController::class, 'index'])->name('user.hub');
    Route::get('/user/booking', fn () => view('user.booking'))->name('user.booking');
    Route::get('/user/history', fn () => view('user.history'))->name('user.history');
    Route::get('/user/chat', fn () => view('user.chat'))->name('user.chat');
    Route::get('/user/profile', fn () => view('user.profile'))->name('user.profile');
    Route::get('/user/borrow', [BorrowController::class, 'index'])->name('user.borrow.index');
    Route::get('/user/borrow/request/{category}', [BorrowController::class, 'create'])->name('user.borrow.create');
    Route::post('/user/borrow/request/{category}', [BorrowController::class, 'store'])->name('user.borrow.store');
    Route::get('/user/borrow/history', [BorrowController::class, 'history'])->name('user.borrow.history');
    Route::get('/user/services/ncd-clinic', [ServiceController::class, 'ncdClinic'])->name('user.services.ncd-clinic');
    Route::get('/user/services/contact', [ServiceController::class, 'contact'])->name('user.services.contact');
    Route::get('/user/services/help', [ServiceController::class, 'help'])->name('user.services.help');
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
