<?php

namespace App\Providers\Filament;

use App\Models\User;
use App\Models\Option;
use App\Filament\Admin\Pages\PublicDashboard;
use DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin;
use DutchCodingCompany\FilamentSocialite\Provider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            // Basic Panel Configuration
            ->default()
            ->id('admin')
            ->path('dashboard')
            ->login(false)
            
            // Branding & Styling
            ->colors([
                'primary' => Color::Amber,
            ])
            ->brandLogo(asset('images/awardslogo.png'))
            ->brandLogoHeight('3rem')
            ->renderHook(
                'panels::head.end',
                fn (): string => \Illuminate\Support\Facades\Vite::useHotFile(public_path('hot'))
                    ->useBuildDirectory('build')
                    ->withEntryPoints(['resources/css/custom.css'])
            )
            
            // Resources & Pages
            ->discoverResources(
                in: app_path('Filament/Admin/Resources'), 
                for: 'App\\Filament\\Admin\\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/Admin/Pages'), 
                for: 'App\\Filament\\Admin\\Pages'
            )
            ->pages([
                PublicDashboard::class,
            ])
            ->homeUrl(fn (): string => PublicDashboard::getUrl())
            
            // Widgets
            ->discoverWidgets(
                in: app_path('Filament/Admin/Widgets'), 
                for: 'App\\Filament\\Admin\\Widgets'
            )
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            
            // Middleware Configuration
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
            ->authGuard('web')
            ->authPasswordBroker('users')
            
            // Reddit OAuth Plugin
            ->plugin($this->configureRedditAuth());
    }

    /**
     * Configure Reddit OAuth authentication
     */
    private function configureRedditAuth(): FilamentSocialitePlugin
    {
        return FilamentSocialitePlugin::make()
            ->providers([
                Provider::make('reddit')
                    ->visible(fn() => true)
                    ->label('Sign in with Reddit')
                    ->icon('heroicon-o-user')
                    ->color('orange'),
            ])
            ->registration()
            ->showDivider(false)
            ->createUserUsing($this->createUserCallback())
            ->resolveUserUsing($this->resolveUserCallback());
    }


    /**
     * Callback for creating new users from Reddit authentication
     */
    private function createUserCallback(): callable
    {
        return function (string $provider, SocialiteUserContract $oauthUser, FilamentSocialitePlugin $plugin) {
            $randomPasswdString = Str::random(64);
            $placeholderPassword = Hash::make($randomPasswdString);

            // Check account age requirement using Socialite data
            $minimumDays = Option::get('account_age_requirement', 30);
            $role = 0; // Default role for new users

            // Get Reddit account creation date from Socialite user data
            if (isset($oauthUser->user['created_utc'])) {
                $createdUtc = $oauthUser->user['created_utc'];
                $accountAgeInDays = (time() - $createdUtc) / 86400; // Convert to days
                
                // If account is too young, set role to -1
                if ($accountAgeInDays < $minimumDays) {
                    $role = -1;
                }
            }

            $user = User::create([
                'name' => $oauthUser->getNickname(),
                'email' => null, // No email required for Reddit users
                'password' => $placeholderPassword,
                'reddit_user' => $oauthUser->getNickname(),
                'role' => $role,
                'flags' => 0, // Default flags
                'avatar' => $oauthUser->getAvatar(),
                'uuid' => Str::uuid(),
            ]);

            return $user;
        };
    }

    /**
     * Callback for resolving existing users from Reddit authentication
     */
    private function resolveUserCallback(): callable
    {
        return function (string $provider, SocialiteUserContract $oauthUser, FilamentSocialitePlugin $plugin) {
            // First try to find by reddit_user field
            $user = User::where('reddit_user', $oauthUser->getNickname())->first();
            
            // Fallback to name field for backward compatibility
            if (!$user) {
                $user = User::where('name', $oauthUser->getNickname())->first();
            }

            if ($user && $user->role === -1) {
                // Check account age requirement for existing users using Socialite data
                $minimumDays = Option::get('account_age_requirement', 30);
                
                if (isset($oauthUser->user['created_utc'])) {
                    $createdUtc = $oauthUser->user['created_utc'];
                    $accountAgeInDays = (time() - $createdUtc) / 86400; // Convert to days
                    
                    // If account now meets age requirement and user was previously restricted (role -1), upgrade to role 0
                    if ($accountAgeInDays >= $minimumDays && $user->role === -1) {
                        $user->role = 0;
                        $user->save();
                    }
                }
            }

            return $user;
        };
    }
}
