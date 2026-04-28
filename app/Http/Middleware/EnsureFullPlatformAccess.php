<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFullPlatformAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = $request->user('admin');

        if (! $admin || ! $admin->hasFullPlatformAccess()) {
            abort(403, 'You do not have access to platform administration.');
        }

        return $next($request);
    }
}
