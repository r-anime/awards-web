<?php

namespace App\Providers\Filament;

use App\Filament\Admin\Pages\PublicDashboard;
use App\Http\Middleware\RedirectUnauthorizedUsers;
use App\Models\Option;
use App\Models\User;
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
            ->favicon(asset('images/pubjury.png'))
            ->renderHook(
                'panels::head.start',
                function (): string {
                    $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
                    $customCss = $manifest['resources/css/custom.css']['file'] ?? '';

                    if ($customCss) {
                        return '<link rel="stylesheet" href="' . asset('build/' . $customCss) . '">';
                    }

                    return '';
                }
            )

            // Resources & Pagesgit
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
            ->homeUrl(fn(): string => PublicDashboard::getUrl())

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
                RedirectUnauthorizedUsers::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->authGuard('web')
            ->authPasswordBroker('users')

            // Reddit OAuth Plugin
            ->plugin($this->configureAuth());
    }

    /**
     * Configure Reddit and AniList OAuth authentication
     */
    private function configureAuth(): FilamentSocialitePlugin
    {
        return FilamentSocialitePlugin::make()
            ->providers([
                Provider::make('reddit')
                    ->visible(fn() => true)
                    ->label('Sign in with Reddit')
                    ->icon('heroicon-o-user')
                    ->color('orange'),
                Provider::make('anilist')
                    ->visible(fn() => true)
                    ->label('Sign in with AniList')
                    ->icon('heroicon-o-user')
                    ->color('blue'),
            ])
            ->registration()
            ->showDivider(false)
            // ->socialiteUserModelClass(\App\Models\SocialiteUser::class)
            ->createUserUsing($this->createUserCallback())
            ->resolveUserUsing($this->resolveUserCallback());
    }

    /**
     * Callback for creating new users from Reddit or AniList authentication
     */
    private function createUserCallback(): callable
    {
        return function (string $provider, SocialiteUserContract $oauthUser, FilamentSocialitePlugin $plugin) {
            $randomPasswdString = Str::random(64);
            $placeholderPassword = Hash::make($randomPasswdString);

            // Check account age requirement using Socialite data
            $minimumDays = Option::get('account_age_requirement', 30);
            $role = 0; // Default role for new users

            $accountAgeInDays = null;
            // Saving username of whichever oauth provider is used
            $redditUser = null;
            $anilistUser = null;

            // Displayed name
            $name = null;


            if ($provider === 'reddit') {
                $redditUser = $oauthUser->getNickname();
                $name = $oauthUser->getNickname();

                // Get Reddit account creation date from Socialite user data
                if (isset($oauthUser->user['created_utc'])) {
                    $createdUtc = (int) $oauthUser->user['created_utc'];
                    $accountAgeInDays = (time() - $createdUtc) / 86400; // Convert to days
                }
            }

            if ($provider === 'anilist') {
                $anilistUser = $oauthUser->getNickname();
                $name = $oauthUser->getNickname();

                // Get AniList account creation date from Socialite user data
                if (isset($oauthUser->user['createdAt'])) {
                    $createdAt = (int) $oauthUser->user['createdAt'];
                    $accountAgeInDays = (time() - $createdAt) / 86400; // Convert to days
                }
            }

            // If account is too young, set role to -1
            if ($accountAgeInDays !== null && $accountAgeInDays < $minimumDays) {
                $role = -1;
            }

            $user = User::create([
                'name' => $name,
                'email' => null,
                'password' => $placeholderPassword,
                'reddit_user' => $redditUser,
                'anilist_user' => $anilistUser,
                'role' => $role,
                'flags' => 0, // Default flags
                'avatar' => $oauthUser->getAvatar(),
                'uuid' => Str::uuid(),
            ]);

            return $user;
        };
    }

    /**
     * Callback for resolving existing users from Reddit and AniList authentication
     */
    private function resolveUserCallback(): callable
    {
        return function (string $provider, SocialiteUserContract $oauthUser, FilamentSocialitePlugin $plugin) {
            $user = null;
            $localUserUuid = config('auth.local_login.user_uuid');

            // TODO: Refresh profile data (name and avatar) and age-based role

            if ($provider == 'reddit') {
                // Find by reddit_user field
                $user = User::where('uuid', '!=', $localUserUuid)
                    ->where('reddit_user', $oauthUser->getNickname())
                    ->first();
            }

            if ($provider == 'anilist') {
                // Find by anilist_user field
                $user = User::where('uuid', '!=', $localUserUuid)
                    ->where('anilist_user', $oauthUser->getNickname())
                    ->first();
            }

            // ! Fallback to name field removed for avoiding ambiguity
            // if (!$user) {
            //     $user = User::where('name', $oauthUser->getNickname())->first();
            // }

            // TODO: Check if this isn't already handled by filament
            // If no user exists yet, create one to ensure an Authenticatable is always returned
            if (!$user) {
                $user = $this->createUserCallback()($provider, $oauthUser, $plugin);
            }

            return $user;
        };
    }
}
