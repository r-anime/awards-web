<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\AppAnswer;
use App\Models\AppScore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationSubmissionWindowTest extends TestCase
{
    use RefreshDatabase;

    private User $applicant;

    protected function setUp(): void
    {
        parent::setUp();

        // A saved answer with a grade on it, so a rejected submission can be shown to change neither
        $this->applicant = User::factory()->create();
        AppAnswer::create(['applicant_id' => $this->applicant->id, 'question_id' => 'q-essay', 'answer' => 'Original answer']);
        AppScore::create([
            'applicant_id' => $this->applicant->id,
            'scorer_id' => User::factory()->create(['role' => 2])->id,
            'question_id' => 'q-essay',
            'score' => 3,
            'comment' => '',
        ]);
    }

    public function test_submissions_are_accepted_while_applications_are_open(): void
    {
        $this->application(now()->subDay(), now()->addDay());

        $this->submitAs($this->applicant)
            ->assertSessionMissing('error');

        // A changed answer replaces the old one and clears its grades, as before
        $this->assertDatabaseHas('app_answers', ['applicant_id' => $this->applicant->id, 'answer' => 'Changed answer']);
        $this->assertDatabaseMissing('app_scores', ['applicant_id' => $this->applicant->id]);
    }

    public function test_submissions_are_rejected_after_the_deadline(): void
    {
        $this->application(now()->subWeek(), now()->subDay());

        $this->submitAs($this->applicant)
            ->assertRedirect(route('application.index'))
            ->assertSessionHas('error', fn ($error) => str_contains($error, 'have closed as of'));

        $this->assertAnswerAndGradeKept();
    }

    public function test_submissions_are_rejected_before_applications_open(): void
    {
        $this->application(now()->addDay(), now()->addWeek());

        $this->submitAs($this->applicant)
            ->assertRedirect(route('application.index'))
            ->assertSessionHas('error', fn ($error) => str_contains($error, 'will open on'));

        $this->assertAnswerAndGradeKept();
    }

    public function test_hosts_can_submit_outside_the_application_period(): void
    {
        $host = User::factory()->create(['role' => 2]);

        foreach ([[now()->addDay(), now()->addWeek()], [now()->subWeek(), now()->subDay()]] as [$start, $end]) {
            Application::query()->delete();
            $this->application($start, $end);

            $this->submitAs($host)
                ->assertSessionMissing('error');
        }

        $this->assertDatabaseHas('app_answers', ['applicant_id' => $host->id, 'answer' => 'Changed answer']);
    }

    private function application($start, $end): void
    {
        Application::create([
            'year' => 2026,
            'start_time' => $start,
            'end_time' => $end,
            'form' => [['id' => 'q-essay', 'type' => 'essay', 'question' => 'Why do you want to help?']],
        ]);
    }

    private function submitAs(User $user)
    {
        return $this->actingAs($user)->post('/participate/application/submit', [
            'question_q-essay' => 'Changed answer',
        ]);
    }

    private function assertAnswerAndGradeKept(): void
    {
        $this->assertDatabaseHas('app_answers', ['applicant_id' => $this->applicant->id, 'answer' => 'Original answer']);
        $this->assertDatabaseHas('app_scores', ['applicant_id' => $this->applicant->id, 'question_id' => 'q-essay']);
    }
}
