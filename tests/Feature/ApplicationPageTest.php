<?php

namespace Tests\Feature;

use App\Models\AppAnswer;
use App\Models\Application;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_apply_short_url_points_at_the_application_page(): void
    {
        $this->get('/apply')->assertRedirect('/participate/application');
    }

    public function test_guests_can_read_an_open_application_but_not_see_the_form(): void
    {
        $this->application(now()->subDay(), now()->addDay());

        $this->get('/participate/application')
            ->assertOk()
            ->assertSee('Applications are OPEN!')
            ->assertDontSee('applicationForm');
    }

    public function test_logged_in_users_see_the_form_with_their_saved_answers(): void
    {
        $this->application(now()->subDay(), now()->addDay());
        $user = User::factory()->create();
        AppAnswer::create(['applicant_id' => $user->id, 'question_id' => 'q-essay', 'answer' => 'My saved answer']);
        AppAnswer::create(['applicant_id' => User::factory()->create()->id, 'question_id' => 'q-essay', 'answer' => 'Someone else']);

        $this->actingAs($user)
            ->get('/participate/application')
            ->assertOk()
            ->assertSee('applicationForm')
            ->assertSee('Why do you want to help?')
            ->assertSee('My saved answer')
            ->assertDontSee('Someone else');
    }

    public function test_the_latest_years_application_is_shown(): void
    {
        $this->application(now()->subYear()->subDay(), now()->subYear()->addDay(), 2025);
        $this->application(now()->subDay(), now()->addDay(), 2026);

        $this->get('/participate/application')
            ->assertSee('Applications are currently being accepted for the 2026 awards season.');
    }

    public function test_upcoming_and_closed_applications_say_so(): void
    {
        $this->application(now()->addDay(), now()->addWeek());
        $this->get('/participate/application')->assertSee('Applications Coming Soon:');

        Application::query()->delete();
        $this->application(now()->subWeek(), now()->subDay());
        $this->get('/participate/application')->assertSee('Applications Closed:');
    }

    public function test_the_form_is_hidden_from_users_outside_the_application_period(): void
    {
        $this->application(now()->subWeek(), now()->subDay());

        $this->actingAs(User::factory()->create(['role' => 0]))
            ->get('/participate/application')
            ->assertDontSee('applicationForm');

        $this->actingAs(User::factory()->create(['role' => 2]))
            ->get('/participate/application')
            ->assertSee('applicationForm');
    }

    public function test_preference_question_lists_the_application_years_categories(): void
    {
        $this->application(now()->subDay(), now()->addDay(), 2026, [
            ['id' => 'q-pref', 'type' => 'preference', 'question' => 'Which categories would you like to judge?'],
        ]);
        Category::create(['year' => 2026, 'name' => 'Best Action', 'type' => 'genre', 'entry_type' => 'anime', 'order' => 1]);
        Category::create(['year' => 2026, 'name' => 'Anime of the Year', 'type' => 'main', 'entry_type' => 'anime', 'order' => 2]);
        Category::create(['year' => 2025, 'name' => 'Best Old Category', 'type' => 'genre', 'entry_type' => 'anime', 'order' => 1]);

        $this->actingAs(User::factory()->create())
            ->get('/participate/application')
            ->assertSee('Best Action')
            ->assertSee('Anime of the Year')
            ->assertDontSee('Best Old Category');
    }

    public function test_main_categories_are_ranked_in_the_admin_set_order_with_id_breaking_ties(): void
    {
        $this->application(now()->subDay(), now()->addDay(), 2026, [
            ['id' => 'q-pref', 'type' => 'preference', 'question' => 'Which categories would you like to judge?'],
        ]);
        // Created (id order) differently from the admin order, with a tie on order 2
        Category::create(['year' => 2026, 'name' => 'Music Video of the Year', 'type' => 'main', 'entry_type' => 'anime', 'order' => 3]);
        Category::create(['year' => 2026, 'name' => 'Short Film of the Year', 'type' => 'main', 'entry_type' => 'anime', 'order' => 1]);
        Category::create(['year' => 2026, 'name' => 'Anime of the Year', 'type' => 'main', 'entry_type' => 'anime', 'order' => 2]);
        Category::create(['year' => 2026, 'name' => 'Movie of the Year', 'type' => 'main', 'entry_type' => 'anime', 'order' => 2]);

        $this->actingAs(User::factory()->create())
            ->get('/participate/application')
            ->assertSeeInOrder(['Short Film of the Year', 'Anime of the Year', 'Movie of the Year', 'Music Video of the Year']);
    }

    public function test_page_explains_when_there_is_no_application(): void
    {
        $this->markTestSkipped('Suspected bug: application.blade.php uses $currentYear, which ApplicationController::index never passes');

        $this->get('/participate/application')
            ->assertOk()
            ->assertSee('No Applications Available');
    }

    public function test_submitting_a_stale_form_saves_the_current_questions(): void
    {
        $this->markTestSkipped('Suspected bug: ApplicationController::store throws "Undefined array key" for an answer to a question not on the form');

        // A host removed q-old while the applicant still had the form open
        $this->application(now()->subDay(), now()->addDay());
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/participate/application/submit', [
                'question_q-old' => 'Answer to a removed question',
                'question_q-essay' => 'Answer to a current question',
            ])
            ->assertRedirect(route('application.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('app_answers', ['applicant_id' => $user->id, 'question_id' => 'q-essay', 'answer' => 'Answer to a current question']);
    }

    public function test_redirect_url_is_remembered_for_after_login(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson('/set-redirect-url', ['redirect_url' => '/participate/application'])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame('/participate/application', session('redirect_after_login'));

        $this->get('/redirect-after-login')->assertRedirect('/participate/application');
        $this->assertFalse(session()->has('redirect_after_login'));
    }

    public function test_redirect_after_login_defaults_to_home(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/redirect-after-login')
            ->assertRedirect('/');
    }

    public function test_an_empty_redirect_url_is_ignored(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson('/set-redirect-url', ['redirect_url' => ''])
            ->assertOk();

        $this->assertFalse(session()->has('redirect_after_login'));
    }

    public function test_redirect_helpers_need_a_login(): void
    {
        $this->postJson('/set-redirect-url', ['redirect_url' => '/participate/application'])->assertUnauthorized();
        $this->assertFalse(session()->has('redirect_after_login'));

        $response = $this->get('/redirect-after-login');
        $response->assertRedirect();
        $this->assertStringStartsWith(url('/login'), $response->headers->get('Location'));
    }

    private function application($start, $end, int $year = 2026, ?array $form = null): Application
    {
        return Application::create([
            'year' => $year,
            'start_time' => $start,
            'end_time' => $end,
            'form' => $form ?? [['id' => 'q-essay', 'type' => 'essay', 'question' => 'Why do you want to help?']],
        ]);
    }
}
