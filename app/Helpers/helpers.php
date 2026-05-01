<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

if (!function_exists('currentClinicId')) {
    function currentClinicId(): int
    {
        // Portal superadmin impersonating a clinic
        if (Auth::guard('portal')->check() && Session::has('portal_impersonating_clinic_id')) {
            return (int) Session::get('portal_impersonating_clinic_id');
        }

        // Session cache (set by SetTenantFromSubdomain middleware)
        if (Session::has('clinic_id')) {
            return (int) Session::get('clinic_id');
        }

        return 1;
    }
}
