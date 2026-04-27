<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class GuardLoginController extends Controller
{
    private const GUARDS = [
        'admin' => [
            'view' => 'auth.admin-login',
            'redirect' => '/admin/dashboard',
            'session_key' => 'admin_id',
        ],
        'staff' => [
            'view' => 'auth.staff-login',
            'redirect' => '/staff/dashboard',
            'session_key' => 'staff_id',
        ],
        'portal' => [
            'view' => 'auth.portal-login',
            'redirect' => '/portal/dashboard',
            'session_key' => 'portal_id',
        ],
        'user' => [
            'view' => 'auth.login',
            'redirect' => '/user/hub',
            'session_key' => 'line_user_id',
            'logout_route' => 'login',
        ],
    ];

    public function show(string $guard)
    {
        abort_unless(array_key_exists($guard, self::GUARDS), 404);

        return view(self::GUARDS[$guard]['view']);
    }

    public function store(Request $request, string $guard)
    {
        abort_unless(array_key_exists($guard, self::GUARDS), 404);

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($guard === 'staff') {
            $credentials['status'] = 'active';
        }

        if (! Auth::guard($guard)->attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::guard($guard)->user();
        $request->session()->put(self::GUARDS[$guard]['session_key'], $user->getAuthIdentifier());

        if (isset($user->clinic_id)) {
            $request->session()->put('clinic_id', $user->clinic_id);
        }

        return redirect()->intended(self::GUARDS[$guard]['redirect']);
    }

    public function destroy(Request $request, string $guard)
    {
        abort_unless(array_key_exists($guard, self::GUARDS), 404);

        Auth::guard($guard)->logout();
        $request->session()->forget(self::GUARDS[$guard]['session_key']);
        $request->session()->regenerateToken();

        return redirect()->route(self::GUARDS[$guard]['logout_route'] ?? $guard.'.login');
    }
}
