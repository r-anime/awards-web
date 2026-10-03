<?php

namespace Tests\Concerns;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as OAuthUser;
use Mockery;

trait LogsInWithOAuth
{
    protected function oauthAccount(string $provider, int $daysOld): OAuthUser
    {
        $createdAt = now()->subDays($daysOld)->timestamp;

        // Shaped like what each provider's mapUserToObject() hands back
        [$id, $name, $raw] = $provider === 'reddit'
            ? ['abc123', 'some_redditor', ['created_utc' => (float) $createdAt]]
            : [4242, 'some_anilister', ['createdAt' => $createdAt]];

        return (new OAuthUser)
            ->setRaw(['id' => $id, 'name' => $name, ...$raw])
            ->map(['id' => $id, 'nickname' => $name, 'name' => $name, 'email' => null, 'avatar' => null]);
    }

    /**
     * Logs in through the real OAuth callback, so the panel's create/resolve
     * callbacks and the socialite_users lookup all run as they do in production.
     */
    protected function login(string $provider, OAuthUser $account): User
    {
        Auth::logout();

        $driver = Mockery::mock();
        $driver->shouldReceive('user')->andReturn($account);
        Socialite::shouldReceive('driver')->with($provider)->once()->andReturn($driver);

        $this->get("/dashboard/oauth/callback/{$provider}")->assertRedirect();
        $this->assertAuthenticated();

        return Auth::user();
    }
}
