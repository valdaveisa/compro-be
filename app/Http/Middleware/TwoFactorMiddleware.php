<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user) {
            // Bypass 2FA for admins
            if ($user->role === 'admin') {
                return $next($request);
            }

            // Allow access to 2FA routes and logout
            if ($request->is('2fa') || $request->is('2fa/*') || $request->routeIs('2fa.*') || $request->routeIs('logout')) {
                return $next($request);
            }

            // If user hasn't set up 2FA or hasn't verified it in session, redirect
            if (empty($user->google2fa_secret) || !session('2fa_verified')) {
                return redirect()->route('2fa.index');
            }
        }

        return $next($request);
    }
}
