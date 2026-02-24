<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->tenant_id) {
            app()->instance('current_tenant', Auth::user()->tenant);
        }

        return $next($request);
    }
}
