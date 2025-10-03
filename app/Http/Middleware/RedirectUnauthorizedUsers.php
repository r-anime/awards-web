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
        $response = $next($request);
        
        // Check if this is a 403 Forbidden response from Filament
        if ($response->getStatusCode() === 403 && $request->is('dashboard*') && !$request->is('login') && !$request->is('dashboard/oauth/*')) {
            if (Auth::check()) {
                $user = Auth::user();
                // Allow role -1 users to stay on dashboard, but redirect others with insufficient permissions
                if ($user->role < 2 && $user->role !== -1) {
                    return redirect('/');
                }
            }
        }
        
        return $response;
    }
}
