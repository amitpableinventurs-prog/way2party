<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class PreferImpersonatedOrganizer
{
    /**
     * When an organizer impersonation session is active, resolve the shared
     * admin/organizer 'web' guard to the impersonated organizer for this
     * request only, so existing Auth::user()/Auth::check() calls throughout
     * the shared admin+organizer controllers work unchanged.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('organizer_impersonate')->check()) {
            Auth::shouldUse('organizer_impersonate');
        }
        return $next($request);
    }
}
