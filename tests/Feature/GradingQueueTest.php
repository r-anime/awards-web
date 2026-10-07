<?php

namespace Tests\Feature;

use App\Filament\Admin\Pages\GradingPage;
use App\Models\Application;
use App\Models\AppAnswer;
use App\Models\AppScore;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GradingQueueTest extends TestCase
{
    use RefreshDatabase;

    private User $host;
    private User $applicant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->application(2025, 'essay-2025');
        $this->application(2026, 'essay-2026');

        $this->host = User::factory()->create(['role' => 2]);
        $this->applicant = User::factory()->create();

        // Applied both years, and the host graded last year's essay
        $this->answer('essay-2025');
        $this->answer('essay-2026');
        $this->score('essay-2025');
    }

    public function test_returning_applicant_is_queued_again_when_graded_only_last_year(): void
    {
        Livewire::actingAs($this->host)
            ->test(GradingPage::class)
            ->assertRedirect(GradingPage::getUrlForUser($this->applicant->uuid));
    }

    public function test_applicant_graded_this_year_is_not_queued(): void
    {
        $this->score('essay-2026');

        Livewire::actingAs($this->host)
            ->test(GradingPage::class)
            ->assertNoRedirect();
    }

    private function application(int $year, string $essayId): void
    {
        Application::create([
            'year' => $year,
            'start_time' => now()->subWeek(),
            'end_time' => now()->addWeek(),
            'form' => [['id' => $essayId, 'type' => 'essay', 'question' => 'Essay']],
        ]);
    }

    private function answer(string $questionId): void
    {
        AppAnswer::create([
            'applicant_id' => $this->applicant->id,
            'question_id' => $questionId,
            'answer' => 'An answer',
        ]);
    }

    private function score(string $questionId): void
    {
        AppScore::create([
            'applicant_id' => $this->applicant->id,
            'scorer_id' => $this->host->id,
            'question_id' => $questionId,
            'score' => 3,
            'comment' => '',
        ]);
    }
}
