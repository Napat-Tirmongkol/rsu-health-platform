<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminActionAccess
{
    public function handle(Request $request, Closure $next, string $action): Response
    {
        $admin = $request->user('admin');

        if (! $admin || ! $admin->hasActionAccess($action)) {
            abort(403, 'You do not have access to this action.');
        }

        return $next($request);
    }
}
