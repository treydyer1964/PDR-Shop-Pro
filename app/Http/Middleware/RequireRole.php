<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireRole
{
    /**
     * Accept one or more role names (comma-separated or multiple args).
     * Aborts 403 if the authenticated user does not have at least one of them.
     *
     * Usage in routes:
     *   Route::middleware('role:owner,bookkeeper')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        if (auth()->user()->hasAnyRole($roles)) {
            return $next($request);
        }

        abort(403, 'You do not have permission to access this page.');
    }
}
