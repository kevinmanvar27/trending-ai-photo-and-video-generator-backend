<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserSuspended
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->isSuspended()) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Your account has been suspended. Reason: ' . auth()->user()->suspension_reason);
        }

        return $next($request);
    }
}
