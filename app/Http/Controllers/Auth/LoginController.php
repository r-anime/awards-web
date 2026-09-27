<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function show(Request $request): View
    {
        // Store redirect parameter in session if present
        if ($request->has('redirect')) {
            $redirectUrl = $request->input('redirect');
            session(['redirect_after_login' => $redirectUrl]);
            \Log::info('LoginController: Storing redirect URL in session: '.$redirectUrl);
        } else {
            \Log::info('LoginController: No redirect parameter found in request');
        }

        return view('auth.login');
    }

    public function local(Request $request): RedirectResponse
    {
        abort_unless(
            app()->environment(['local', 'testing']) && config('auth.local_login.enabled'),
            404,
        );

        $user = User::query()
            ->where('uuid', config('auth.local_login.user_uuid'))
            ->first();

        if ($user === null) {
            return back()->withErrors([
                'local_login' => 'The local administrator account is missing. Run php artisan db:seed --class=DevelopmentUserSeeder.',
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }
}
