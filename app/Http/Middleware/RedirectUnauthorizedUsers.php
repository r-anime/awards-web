<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectUnauthorizedUsers
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If a role -1 user hits any participate route, send them to the dashboard home directly
        if ($request->is('participate') || $request->is('participate/*')) {
            if (Auth::check()) {
                $user = Auth::user();
                if ($user && (int) $user->role === -1) {
                    return redirect()->to('/dashboard');
                }
            }
        }
        
        // Proactively block access to dashboard admin pages for users with insufficient roles
        if ($request->is('dashboard*') && !$request->is('dashboard') && !$request->is('login') && !$request->is('dashboard/oauth/*')) {
            if (Auth::check()) {
                $user = Auth::user();
                if ($user && (int) $user->role < 2) {
                    // Redirect users with role < 2 to the main dashboard page
                    return redirect('/dashboard');
                }
            }
        }
        
        $response = $next($request);

        // Handle 403s for non -1 users with insufficient permissions (fallback)
        if ($response->getStatusCode() === 403 && $request->is('dashboard*') && !$request->is('login') && !$request->is('dashboard/oauth/*')) {
            if (Auth::check()) {
                $user = Auth::user();
                if ($user && (int) $user->role < 2 && (int) $user->role !== -1) {
                    return redirect('/');
                }
            }
        }

        return $response;
    }
}
