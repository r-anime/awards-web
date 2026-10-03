<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectUnauthorizedUsers
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If a restricted user hits any participate route, send them to the dashboard home directly
        if ($request->is('participate') || $request->is('participate/*')) {
            if (Auth::check()) {
                $user = Auth::user();
                if ($user && $user->isRestricted()) {
                    return new RedirectResponse(url('/dashboard'));
                }
            }
        }

        // This middleware now works as a fallback since CheckUserRole handles the main blocking
        // Keep this for any edge cases or specific route handling

        $response = $next($request);

        // Handle 403s for non-restricted users with insufficient permissions (fallback)
        if ($response->getStatusCode() === 403 && $request->is('dashboard/*') && ! $request->is('login') && ! $request->is('dashboard/logout') && ! $request->is('dashboard/oauth/*')) {
            if (Auth::check()) {
                $user = Auth::user();
                if ($user && (int) $user->role < 2 && ! $user->isRestricted()) {
                    return new RedirectResponse(url('/'));
                }
            }
        }

        return $response;
    }
}
