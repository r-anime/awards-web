<?php

namespace Tests\Feature;

use Database\Seeders\DevelopmentUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_local_login_authenticates_the_seeded_administrator(): void
    {
        $this->seed(DevelopmentUserSeeder::class);

        $response = $this
            ->withSession(['url.intended' => '/dashboard/acknowledgements'])
            ->post('/local-login');

        $response->assertRedirect('/dashboard/acknowledgements');
        $this->assertAuthenticated();
        $this->assertSame(2, auth()->user()->role);
        $this->assertSame(config('auth.local_login.user_uuid'), auth()->user()->uuid);
    }

    public function test_local_login_reports_when_the_seeded_administrator_is_missing(): void
    {
        $this->from('/login')
            ->post('/local-login')
            ->assertRedirect('/login')
            ->assertSessionHasErrors('local_login');

        $this->assertGuest();
    }

    public function test_local_login_is_rejected_when_disabled(): void
    {
        $this->withoutVite();

        $this->seed(DevelopmentUserSeeder::class);
        config()->set('auth.local_login.enabled', false);

        $this->post('/local-login')->assertNotFound();
        $this->assertGuest();
    }

    public function test_development_user_cannot_be_seeded_in_production(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The development user can only be seeded in local or testing environments.');

        (new DevelopmentUserSeeder)->run();
    }
}
