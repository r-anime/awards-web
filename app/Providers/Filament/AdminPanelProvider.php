<?php

namespace App\Providers\Filament;

use App\Models\User;
use DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin;
use DutchCodingCompany\FilamentSocialite\Provider;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugin(
                FilamentSocialitePlugin::make()
                    ->providers([
                        Provider::make('reddit')
                            ->visible(fn() => true),
                    ])
                    ->registration()
                    ->createUserUsing(function (string $provider, SocialiteUserContract $oauthUser, FilamentSocialitePlugin $plugin) {
                        // Logic to create a new user.

                        $randomPasswdString = Str::random(64); // Generate a random string
                        $placeholderPassword = Hash::make($randomPasswdString); // Hash the random string

                        $randomEmailString = Str::random(16); // Generate a random string

                        $user = User::create([
                            'name' => $oauthUser->getNickname(),
                            'email' => $randomEmailString.'@awards-web.com',
                            'password' => $placeholderPassword,
                            'role' => 0, // Default role for new users
                        ]);
                        return $user;
                    })
                    ->resolveUserUsing(function (string $provider, SocialiteUserContract $oauthUser, FilamentSocialitePlugin $plugin) {
                        // Logic to retrieve an existing user.
                        // $user = User::where('email', $oauthUser->getEmail())->first();
                        $user = User::where('name', $oauthUser->getNickname())->first();
                        return $user;
                    })


            );
    }
}
