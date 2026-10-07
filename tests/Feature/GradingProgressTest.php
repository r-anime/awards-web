<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\ApplicationResource\Pages\ApplicationGrading;
use App\Filament\Admin\Resources\ApplicationResource\Pages\HostGradingProgress;
use App\Models\AppAnswer;
use App\Models\Application;
use App\Models\AppScore;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GradingProgressTest extends TestCase
{
    use RefreshDatabase;

    private User $fullAnswers;

    private User $partialAnswers;

    private User $noEssays;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        session(['selected-year-filter' => app('current-year')]);

        Application::create([
            'year' => app('current-year'),
            'start_time' => now()->subWeek(),
            'end_time' => now()->addWeek(),
            'form' => [
                ['id' => 'essay-1', 'type' => 'essay', 'question' => 'First essay'],
                ['id' => 'essay-2', 'type' => 'essay', 'question' => 'Second essay'],
                ['id' => 'choice-1', 'type' => 'multiple_choice', 'question' => 'Pick one', 'options' => []],
            ],
        ]);

        $this->fullAnswers = User::factory()->create();
        $this->answer($this->fullAnswers, 'essay-1', 'An answer');
        $this->answer($this->fullAnswers, 'essay-2', 'Another answer');

        $this->partialAnswers = User::factory()->create();
        $this->answer($this->partialAnswers, 'essay-1', 'An answer');
        $this->answer($this->partialAnswers, 'essay-2', '');

        $this->noEssays = User::factory()->create();
        $this->answer($this->noEssays, 'choice-1', 'some-option');
    }

    public function test_host_progress_counts_graded_and_remaining_applications_and_essays(): void
    {
        $alice = User::factory()->create(['name' => 'Alice', 'role' => 2]);
        $bob = User::factory()->create(['name' => 'Bob', 'role' => 2]);
        User::factory()->create(['name' => 'Juror Jo', 'role' => 1]);

        // Only hosts grade, so moderators and admins aren't listed even with scores
        $moderator = User::factory()->create(['name' => 'Moderator Mia', 'role' => 3]);
        $admin = User::factory()->create(['name' => 'Admin Andy', 'role' => 4]);
        $this->score($this->fullAnswers, $moderator, 'essay-1');
        $this->score($this->fullAnswers, $admin, 'essay-1');

        // Alice graded everything, plus a score on an empty answer that shouldn't count
        $this->score($this->fullAnswers, $alice, 'essay-1');
        $this->score($this->fullAnswers, $alice, 'essay-2');
        $this->score($this->partialAnswers, $alice, 'essay-1');
        $this->score($this->partialAnswers, $alice, 'essay-2');

        // Bob has one legacy score (old numeric question_id, form id in question_uuid) and one from another year's form
        AppScore::create([
            'applicant_id' => $this->fullAnswers->id,
            'scorer_id' => $bob->id,
            'question_id' => '7',
            'question_uuid' => 'essay-1',
            'score' => 3,
            'comment' => '',
        ]);
        $this->score($this->partialAnswers, $bob, 'old-essay');

        $progress = Livewire::actingAs($alice)
            ->test(HostGradingProgress::class)
            ->assertSee('Alice')
            ->assertDontSee('Juror Jo')
            ->assertDontSee('Moderator Mia')
            ->assertDontSee('Admin Andy')
            ->assertSee('2 applications to grade for')
            ->assertSee('Q1 Graded (of 2)')
            ->assertSee('Q2 Graded (of 1)')
            ->instance()
            ->getHostProgress();

        $this->assertSame(2, $progress['total_applications']);
        $this->assertSame([
            ['id' => 'essay-1', 'label' => 'Q1', 'question' => 'First essay', 'answered' => 2],
            ['id' => 'essay-2', 'label' => 'Q2', 'question' => 'Second essay', 'answered' => 1],
        ], $progress['questions']);
        $this->assertSame([
            [
                'name' => 'Alice',
                'applications_graded' => 2,
                'applications_remaining' => 0,
                'grades' => ['essay-1' => 2, 'essay-2' => 1],
            ],
            [
                'name' => 'Bob',
                'applications_graded' => 1,
                'applications_remaining' => 1,
                'grades' => ['essay-1' => 1, 'essay-2' => 0],
            ],
        ], $progress['hosts']);
    }

    public function test_grading_overview_shows_and_sorts_by_average_score(): void
    {
        $host = User::factory()->create(['role' => 2]);
        $otherHost = User::factory()->create(['role' => 2]);
        $this->score($this->fullAnswers, $host, 'essay-1', 1);
        $this->score($this->fullAnswers, $otherHost, 'essay-1', 2);
        $this->score($this->partialAnswers, $host, 'essay-1', 5);

        Livewire::actingAs($host)
            ->test(ApplicationGrading::class)
            ->assertTableColumnStateSet('question_1', '1.50', $this->fullAnswers)
            ->assertTableColumnStateSet('question_1', '5.00', $this->partialAnswers)
            ->assertTableColumnStateSet('question_2', 'No grades', $this->fullAnswers)
            ->sortTable('question_1')
            ->assertCanSeeTableRecords([$this->fullAnswers, $this->partialAnswers], inOrder: true)
            ->sortTable('question_1', 'desc')
            ->assertCanSeeTableRecords([$this->partialAnswers, $this->fullAnswers], inOrder: true);
    }

    public function test_host_progress_page_is_routed_separately_from_the_grading_page(): void
    {
        $host = User::factory()->create(['name' => 'Alice', 'role' => 2]);

        $this->actingAs($host)
            ->get(HostGradingProgress::getUrl())
            ->assertOk()
            ->assertSee('Host Grading Progress');
    }

    public function test_host_progress_shows_empty_state_without_an_application_form(): void
    {
        $host = User::factory()->create(['role' => 2]);
        session(['selected-year-filter' => 1999]);

        Livewire::actingAs($host)
            ->test(HostGradingProgress::class)
            ->assertSee('No Application Form for 1999');
    }

    public function test_grading_overview_shows_which_essays_each_applicant_answered(): void
    {
        $host = User::factory()->create(['role' => 2]);

        Livewire::actingAs($host)
            ->test(ApplicationGrading::class)
            ->assertCanSeeTableRecords([$this->fullAnswers, $this->partialAnswers])
            ->assertCanNotSeeTableRecords([$this->noEssays])
            ->assertTableColumnStateSet('answered_1', true, $this->fullAnswers)
            ->assertTableColumnStateSet('answered_2', true, $this->fullAnswers)
            ->assertTableColumnStateSet('answered_1', true, $this->partialAnswers)
            ->assertTableColumnStateSet('answered_2', false, $this->partialAnswers);
    }

    private function answer(User $applicant, string $questionId, string $answer): void
    {
        AppAnswer::create([
            'applicant_id' => $applicant->id,
            'question_id' => $questionId,
            'answer' => $answer,
        ]);
    }

    private function score(User $applicant, User $scorer, string $questionId, int $score = 3): void
    {
        AppScore::create([
            'applicant_id' => $applicant->id,
            'scorer_id' => $scorer->id,
            'question_id' => $questionId,
            'score' => $score,
            'comment' => '',
        ]);
    }
}
