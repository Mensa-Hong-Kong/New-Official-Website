<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdministrator
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (
            $request->user()->getAllPermissions()->count() > 0 ||
            $request->user()->hasRole('Super Administrator')
        ) {
            return $next($request);
        }
        abort(403);
    }
}
