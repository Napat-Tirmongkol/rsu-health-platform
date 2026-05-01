<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenantFromSubdomain
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        // ตัวอย่าง: medical.rsu.ac.th -> แยกเอา medical ออกมา
        // ถ้าเป็น localhost หรือ IP ให้ใช้ default (medical)
        $subdomain = explode('.', $host)[0];

        if (!in_array($subdomain, ['127', 'localhost', 'rsu-health-platform'])) {
            $clinic = \DB::table('sys_clinics')
                ->where('slug', $subdomain)
                ->where('status', 'active')
                ->first();

            if ($clinic) {
                session(['clinic_id' => $clinic->id]);
            }
        } else {
            // Default สำหรับ Local Development
            session(['clinic_id' => 1]);
        }

        return $next($request);
    }
}
