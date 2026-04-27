<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalLoginController extends Controller
{
    /**
     * Show the portal login form.
     */
    public function showLoginForm()
    {
        return view('auth.portal-login');
    }

    /**
     * Handle a portal login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('portal')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended('/portal/dashboard');
        }

        return back()->withErrors([
            'email' => 'อีเมล หรือ รหัสผ่านไม่ถูกต้อง',
        ])->onlyInput('email');
    }

    /**
     * Log the portal user out.
     */
    public function logout(Request $request)
    {
        Auth::guard('portal')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/portal/login');
    }
}
