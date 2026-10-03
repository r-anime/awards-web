<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\LogsInWithOAuth;
use Tests\TestCase;

class OAuthAccountLinkingTest extends TestCase
{
    use LogsInWithOAuth, RefreshDatabase;

    // Hosts link accounts by filling in the other provider's field in Edit User
    public function test_a_host_can_link_a_reddit_account_to_an_anilist_user(): void
    {
        $anilist = $this->oauthAccount('anilist', daysOld: 60);
        $reddit = $this->oauthAccount('reddit', daysOld: 60);

        $user = $this->login('anilist', $anilist);
        $user->update(['reddit_user' => $reddit->getNickname()]);

        $this->assertTrue($this->login('reddit', $reddit)->is($user));
        $this->assertTrue($this->login('anilist', $anilist)->is($user));
        $this->assertDatabaseCount('users', 1);
    }

    public function test_a_host_can_link_an_anilist_account_to_a_reddit_user(): void
    {
        $reddit = $this->oauthAccount('reddit', daysOld: 60);
        $anilist = $this->oauthAccount('anilist', daysOld: 60);

        $user = $this->login('reddit', $reddit);
        $user->update(['anilist_id' => $anilist->getId()]);

        $this->assertTrue($this->login('anilist', $anilist)->is($user));
        $this->assertTrue($this->login('reddit', $reddit)->is($user));
        $this->assertDatabaseCount('users', 1);
    }
}
