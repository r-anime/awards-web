<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function show(Request $request)
    {
        // Store redirect parameter in session if present
        if ($request->has('redirect')) {
            $redirectUrl = $request->input('redirect');
            session(['redirect_after_login' => $redirectUrl]);
            \Log::info('LoginController: Storing redirect URL in session: ' . $redirectUrl);
        } else {
            \Log::info('LoginController: No redirect parameter found in request');
        }
        
        return view('auth.login');
    }
}
