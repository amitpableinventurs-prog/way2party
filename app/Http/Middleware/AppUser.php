<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Session;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AppUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $guard = 'appuser')
    {

        if (!Auth::guard($guard)->check()) {
            Session::put('url.intended', url()->current());
            return redirect('user/login');
        }
        return $next($request);
    }
}
