<?php

use Illuminate\Support\Facades\Session;

if (!function_exists('currentClinicId')) {
    /**
     * Get the current active clinic ID.
     *
     * @return int
     */
    function currentClinicId(): int
    {
        // 1. Session cache
        if (Session::has('clinic_id')) {
            return (int) Session::get('clinic_id');
        }

        // 2. Subdomain lookup (Logic to be implemented in middleware)
        // For now, fallback to default
        
        // 3. Fallback to RSU Medical Clinic (ID 1)
        return 1;
    }
}
