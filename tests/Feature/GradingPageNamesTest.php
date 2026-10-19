<?php

namespace Tests\Feature;

use App\Filament\Admin\Pages\GradingPage;
use App\Filament\Admin\Resources\ApplicationResource\Pages\ApplicationGrading;
use App\Models\Application;
use App\Models\Option;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GradingPageNamesTest extends TestCase
{
    use RefreshDatabase;

    private User $applicant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Application::create([
            'year' => app('current-year'),
            'start_time' => now()->subWeek(),
            'end_time' => now()->addWeek(),
            'form' => [
                ['id' => 'essay-1', 'type' => 'essay', 'question' => 'First essay'],
            ],
        ]);

        $this->applicant = User::factory()->create(['name' => 'Secret Applicant', 'reddit_user' => 'secret_applicant']);
    }

    public function test_hosts_grade_without_seeing_the_applicants_name(): void
    {
        $this->actingAs(User::factory()->create(['role' => 2]));

        $this->gradingPage()
            ->assertSee($this->applicant->uuid)
            ->assertDontSee('Secret Applicant')
            ->assertDontSee('secret_applicant');
    }

    public function test_hosts_still_grade_blind_when_names_are_shown_to_hosts(): void
    {
        Option::set('show_applicant_names_to_hosts', 'true');
        $this->actingAs(User::factory()->create(['role' => 2]));

        $this->gradingPage()
            ->assertDontSee('Secret Applicant')
            ->assertDontSee('secret_applicant');
    }

    public function test_moderators_see_the_applicants_name(): void
    {
        $this->actingAs(User::factory()->create(['role' => 3]));

        $this->gradingPage()->assertSee('Secret Applicant');
    }

    public function test_grading_overview_hides_names_from_hosts_even_when_names_are_shown_to_hosts(): void
    {
        Option::set('show_applicant_names_to_hosts', 'true');
        session(['selected-year-filter' => app('current-year')]);
        $this->actingAs(User::factory()->create(['role' => 2]));

        Livewire::test(ApplicationGrading::class)
            ->assertTableColumnDoesNotExist('name')
            ->assertSee('Usernames are hidden');
    }

    public function test_grading_overview_shows_names_to_moderators(): void
    {
        session(['selected-year-filter' => app('current-year')]);
        $this->actingAs(User::factory()->create(['role' => 3]));

        Livewire::test(ApplicationGrading::class)
            ->assertTableColumnExists('name');
    }

    private function gradingPage()
    {
        return Livewire::test(GradingPage::class, ['userUuid' => $this->applicant->uuid]);
    }
}
