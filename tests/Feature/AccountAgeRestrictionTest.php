<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Option;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\LogsInWithOAuth;
use Tests\TestCase;

class AccountAgeRestrictionTest extends TestCase
{
    use LogsInWithOAuth, RefreshDatabase;

    private const REQUIRED_DAYS = 30;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        Option::set('account_age_requirement', self::REQUIRED_DAYS);

        Application::create([
            'year' => 2026,
            'start_time' => now()->subDay(),
            'end_time' => now()->addMonths(3),
            'form' => [[
                'id' => 'q-essay',
                'type' => 'essay',
                'question' => 'Why do you want to help?',
            ]],
        ]);
    }

    public static function oauthProviders(): array
    {
        return [
            'reddit' => ['reddit'],
            'anilist' => ['anilist'],
        ];
    }

    #[DataProvider('oauthProviders')]
    public function test_an_account_old_enough_can_apply(string $provider): void
    {
        $this->login($provider, $this->oauthAccount($provider, daysOld: 60));

        $this->assertCanApply();
    }

    #[DataProvider('oauthProviders')]
    public function test_a_too_young_account_cannot_apply(string $provider): void
    {
        $this->login($provider, $this->oauthAccount($provider, daysOld: 5));

        $this->assertCannotApply();
    }

    #[DataProvider('oauthProviders')]
    public function test_the_restriction_lifts_on_the_next_login_once_the_account_is_old_enough(string $provider): void
    {
        $account = $this->oauthAccount($provider, daysOld: 20);

        $user = $this->login($provider, $account);
        $this->assertCannotApply();

        $this->travel(11)->days();
        $this->assertTrue($this->login($provider, $account)->is($user));

        $this->assertCanApply();
    }

    #[DataProvider('oauthProviders')]
    public function test_the_restriction_stays_on_the_next_login_while_the_account_is_still_too_young(string $provider): void
    {
        $account = $this->oauthAccount($provider, daysOld: 20);

        $this->login($provider, $account);
        $this->travel(5)->days();
        $this->login($provider, $account);

        $this->assertCannotApply();
    }

    #[DataProvider('oauthProviders')]
    public function test_raising_the_requirement_restricts_existing_users_on_their_next_login(string $provider): void
    {
        $account = $this->oauthAccount($provider, daysOld: 60);

        $this->login($provider, $account);
        Option::set('account_age_requirement', 90);
        $this->login($provider, $account);

        $this->assertCannotApply();
    }

    #[DataProvider('oauthProviders')]
    public function test_a_manual_restriction_survives_logging_in_again(string $provider): void
    {
        $account = $this->oauthAccount($provider, daysOld: 60);

        $this->restrictManually($this->login($provider, $account));
        $this->login($provider, $account);

        $this->assertCannotApply();
    }

    #[DataProvider('oauthProviders')]
    public function test_an_exception_granted_by_a_host_survives_logging_in_again(string $provider): void
    {
        $account = $this->oauthAccount($provider, daysOld: 5);

        $this->grantException($this->login($provider, $account));
        $this->login($provider, $account);

        $this->assertCanApply();
    }

    // Hosts restrict through the Role Level select in Edit User
    private function restrictManually(User $user): void
    {
        $user->update(['role' => -1]);
    }

    private function grantException(User $user): void
    {
        $user->exemptFromAgeCheck();
    }

    private function assertCanApply(): void
    {
        $this->get('/apply')->assertRedirect('/participate/application');
        $this->get('/participate/application')->assertOk();

        $this->post('/participate/application/submit', ['question_q-essay' => 'I like anime.'])
            ->assertRedirect(route('application.index'));
        $this->assertDatabaseHas('app_answers', ['applicant_id' => Auth::id(), 'question_id' => 'q-essay']);
    }

    private function assertCannotApply(): void
    {
        $this->get('/apply')->assertRedirect('/participate/application');
        $this->get('/participate/application')->assertRedirect('/dashboard');

        $this->post('/participate/application/submit', ['question_q-essay' => 'I like anime.'])
            ->assertRedirect('/dashboard');
        $this->assertDatabaseMissing('app_answers', ['applicant_id' => Auth::id()]);
    }
}
