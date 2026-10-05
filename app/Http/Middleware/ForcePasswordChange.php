<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class ForcePasswordChange
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {

            if (Auth::user()->must_change_password) {

                if (! $request->routeIs('password.change')) {
                    return redirect()->route('password.change');
                }
            }

        }

        return $next($request);
    }
}