<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\UserResource\Pages\EditUser;
use App\Filament\Admin\Resources\UserResource\Pages\ListUsers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create(['role' => 2]));
    }

    public function test_a_reddit_username_already_in_use_is_rejected(): void
    {
        User::factory()->create(['reddit_user' => 'taken']);
        $user = User::factory()->create();

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->fillForm(['reddit_user' => 'taken'])
            ->call('save')
            ->assertHasFormErrors(['reddit_user' => 'unique']);
    }

    public function test_an_anilist_id_already_in_use_is_rejected(): void
    {
        User::factory()->create(['anilist_id' => 4242]);
        $user = User::factory()->create();

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->fillForm(['anilist_id' => 4242])
            ->call('save')
            ->assertHasFormErrors(['anilist_id' => 'unique']);
    }

    public function test_saving_a_user_keeps_its_own_values(): void
    {
        $user = User::factory()->create(['reddit_user' => 'mine', 'anilist_id' => 4242]);

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->call('save')
            ->assertHasNoFormErrors();
    }

    public function test_empty_provider_fields_are_not_treated_as_duplicates(): void
    {
        User::factory()->create(['reddit_user' => null, 'anilist_id' => null]);
        $user = User::factory()->create(['reddit_user' => 'clear_me', 'anilist_id' => 4242]);

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->fillForm(['reddit_user' => '', 'anilist_id' => ''])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'reddit_user' => null, 'anilist_id' => null]);
    }

    public function test_a_host_can_grant_an_age_exception(): void
    {
        $user = User::factory()->create(['flags' => User::FLAG_AGE_RESTRICTED]);

        Livewire::test(ListUsers::class)
            ->callTableAction('grantAgeException', $user);

        $user->refresh();
        $this->assertFalse($user->isAgeRestricted());
        $this->assertTrue($user->isAgeExempt());
    }

    public function test_the_age_exception_action_only_shows_for_age_restricted_users(): void
    {
        $user = User::factory()->create();

        Livewire::test(ListUsers::class)
            ->assertTableActionHidden('grantAgeException', $user);
    }

    public function test_age_restricted_users_can_be_filtered(): void
    {
        $restricted = User::factory()->create(['flags' => User::FLAG_AGE_RESTRICTED]);
        $exempt = User::factory()->create(['flags' => User::FLAG_AGE_EXEMPT]);

        Livewire::test(ListUsers::class)
            ->filterTable('age_restricted')
            ->assertCanSeeTableRecords([$restricted])
            ->assertCanNotSeeTableRecords([$exempt]);
    }
}
