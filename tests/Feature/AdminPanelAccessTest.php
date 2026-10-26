<?php

namespace Tests\Feature;

use App\Filament\Admin\Pages\FinalVoteResults;
use App\Filament\Admin\Pages\NominationResults;
use App\Filament\Admin\Pages\PublicDashboard;
use App\Filament\Admin\Resources\ApplicationResource;
use App\Filament\Admin\Resources\CategoryResource;
use App\Filament\Admin\Resources\EntryResource;
use App\Filament\Admin\Resources\OptionResource;
use App\Filament\Admin\Resources\ResultResource;
use App\Models\Application;
use App\Models\Category;
use App\Models\Entry;
use App\Models\Result;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * List, create and edit pages that need at least the host role
     */
    private array $hostPages;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $category = Category::create(['year' => 2026, 'name' => 'Best Animation', 'type' => 'production', 'entry_type' => 'anime', 'order' => 1]);
        $entry = Entry::create(['type' => 'anime', 'name' => 'Some Show', 'year' => 2026, 'image' => 'entry/some-show.jpg']);
        $result = Result::create([
            'year' => 2026,
            'category_id' => $category->id,
            'name' => 'Some Show',
            'image' => 'entry/some-show.jpg',
            'entry_id' => $entry->id,
            'jury_rank' => 1,
            'public_rank' => 1,
            'description' => 'A writeup',
        ]);
        $application = Application::create([
            'year' => 2026,
            'start_time' => now()->subWeek(),
            'end_time' => now()->addWeek(),
            'form' => [],
        ]);

        $this->hostPages = [
            CategoryResource::getUrl('index'),
            CategoryResource::getUrl('create'),
            CategoryResource::getUrl('edit', ['record' => $category]),
            EntryResource::getUrl('index'),
            EntryResource::getUrl('create'),
            EntryResource::getUrl('edit', ['record' => $entry]),
            ResultResource::getUrl('index'),
            ResultResource::getUrl('create'),
            ResultResource::getUrl('edit', ['record' => $result]),
            ApplicationResource::getUrl('index'),
            ApplicationResource::getUrl('create'),
            ApplicationResource::getUrl('edit', ['record' => $application]),
            NominationResults::getUrl(),
            FinalVoteResults::getUrl(),
            OptionResource::getUrl('index'),
        ];
    }

    public function test_guests_are_sent_to_login_from_every_admin_page(): void
    {
        foreach ([...$this->hostPages, PublicDashboard::getUrl()] as $url) {
            $response = $this->get($url);

            $response->assertRedirect();
            $this->assertStringStartsWith(url('/login'), $response->headers->get('Location'), $url);
        }
    }

    public function test_users_and_jurors_are_sent_home_from_host_pages(): void
    {
        foreach ([0, 1] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));

            foreach ($this->hostPages as $url) {
                $this->get($url)->assertRedirect('/');
            }
        }
    }

    public function test_restricted_users_are_refused_host_pages(): void
    {
        $restricted = [
            User::factory()->create(['role' => -1]),
            User::factory()->create(['role' => 0, 'flags' => User::FLAG_AGE_RESTRICTED]),
        ];

        foreach ($restricted as $user) {
            $this->actingAs($user);

            foreach ($this->hostPages as $url) {
                $this->get($url)->assertForbidden();
            }
        }
    }

    public function test_hosts_and_above_can_open_host_pages_including_options(): void
    {
        foreach ([2, 3, 4] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));

            foreach ($this->hostPages as $url) {
                $this->get($url)->assertOk();
            }
        }
    }

    public function test_can_access_follows_role(): void
    {
        $resources = [
            CategoryResource::class => 2,
            EntryResource::class => 2,
            ResultResource::class => 2,
            ApplicationResource::class => 2,
            NominationResults::class => 2,
            FinalVoteResults::class => 2,
            OptionResource::class => 2,
        ];

        foreach ([-1, 0, 1, 2, 3, 4] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));

            foreach ($resources as $resource => $minimumRole) {
                $this->assertSame($role >= $minimumRole, $resource::canAccess(), "{$resource} for role {$role}");
            }
        }
    }

    public function test_every_logged_in_user_can_open_the_public_dashboard(): void
    {
        $users = [
            User::factory()->create(['role' => -1]),
            User::factory()->create(['role' => 0, 'flags' => User::FLAG_AGE_RESTRICTED]),
            User::factory()->create(['role' => 0]),
            User::factory()->create(['role' => 1]),
            User::factory()->create(['role' => 2]),
        ];

        foreach ($users as $user) {
            $this->actingAs($user)->get(PublicDashboard::getUrl())->assertOk();
        }
    }

    public function test_public_dashboard_shows_a_message_matching_the_users_access(): void
    {
        $this->actingAs(User::factory()->create(['role' => 0]))
            ->get(PublicDashboard::getUrl())
            ->assertSee('Limited Access')
            ->assertDontSee('Host Access Available')
            ->assertDontSee('Account Age Restriction');

        $this->actingAs(User::factory()->create(['role' => 0, 'flags' => User::FLAG_AGE_RESTRICTED]))
            ->get(PublicDashboard::getUrl())
            ->assertSee('Account Age Restriction')
            ->assertDontSee('Limited Access');

        $this->actingAs(User::factory()->create(['role' => 2]))
            ->get(PublicDashboard::getUrl())
            ->assertSee('Host Access Available')
            ->assertDontSee('Limited Access');
    }
}
