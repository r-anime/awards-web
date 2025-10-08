<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  int  $requiredRole
     */
    public function handle(Request $request, Closure $next, int $requiredRole = 2): Response
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        // Skip role check for the main dashboard page, public dashboard, login, and logout
        if ($request->is('dashboard') || 
            $request->is('dashboard/public-dashboard') || 
            $request->is('dashboard/login') || 
            $request->is('dashboard/logout') ||
            $request->is('dashboard/oauth/*')) {
            return $next($request);
        }

        $user = Auth::user();
        
        if ($user->role < $requiredRole) {
            // Return 403 Forbidden for API requests or AJAX
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'Insufficient permissions'], 403);
            }
            
            // Redirect to dashboard for regular requests
            return redirect('/dashboard');
        }

        return $next($request);
    }
}
