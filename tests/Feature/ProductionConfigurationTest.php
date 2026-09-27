<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProductionConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_safe_production_configuration_passes(): void
    {
        $this->setProductionEnvironment();

        $this->artisan('app:verify-production-config')
            ->expectsOutput('Production configuration is safe to deploy.')
            ->assertSuccessful();
    }

    public function test_non_production_environment_is_rejected(): void
    {
        config()->set('auth.local_login.enabled', false);

        $this->artisan('app:verify-production-config')
            ->expectsOutput('Deployment aborted: APP_ENV must be production.')
            ->assertFailed();
    }

    public function test_enabled_local_login_is_rejected(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');
        config()->set('auth.local_login.enabled', true);

        $this->artisan('app:verify-production-config')
            ->expectsOutput('Deployment aborted: LOCAL_LOGIN_ENABLED must be false.')
            ->assertFailed();
    }

    public function test_development_administrator_is_rejected(): void
    {
        $this->setProductionEnvironment();

        User::query()->create([
            'uuid' => config('auth.local_login.user_uuid'),
            'name' => 'Local Administrator',
            'password' => Hash::make('unused'),
            'role' => 2,
            'flags' => 0,
        ]);

        $this->artisan('app:verify-production-config')
            ->expectsOutput('Deployment aborted: the local development administrator exists in the production database.')
            ->assertFailed();
    }

    private function setProductionEnvironment(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');
        config()->set('auth.local_login.enabled', false);
    }
}
