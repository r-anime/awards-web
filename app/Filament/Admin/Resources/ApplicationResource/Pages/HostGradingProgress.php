<?php

namespace App\Filament\Admin\Resources\ApplicationResource\Pages;

use App\Filament\Admin\Resources\ApplicationResource;
use App\Models\AppAnswer;
use App\Models\Application;
use App\Models\AppScore;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Attributes\On;

class HostGradingProgress extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = ApplicationResource::class;

    protected string $view = 'filament.resources.application-resource.pages.host-grading-progress';

    protected static ?string $title = 'Host Grading Progress';

    protected static ?string $navigationLabel = 'Host Grading Progress';

    public function getApplication(): ?Application
    {
        $filterYear = session('selected-year-filter') ?? intval(app('current-year'));

        return Application::where('year', $filterYear)->first();
    }

    #[On('filter-year-updated')]
    public function refreshOnYearFilter()
    {
        // This will trigger a re-render of the page
    }

    public function table(Table $table): Table
    {
        $application = $this->getApplication();

        if (! $application) {
            $filterYear = session('selected-year-filter') ?? intval(app('current-year'));

            return $table
                ->records(fn () => [])
                ->emptyStateHeading("No Application Form for {$filterYear}")
                ->emptyStateDescription("Create an application form first to track grading progress for the {$filterYear} awards.");
        }

        // Calculated once per request, since the description, columns and rows all use it
        $progress = $this->getHostProgress();

        $columns = [
            TextColumn::make('name')->label('Host'),
            TextColumn::make('applications_graded')->label('Applications Graded'),
            TextColumn::make('applications_remaining')->label('Applications Remaining'),
        ];

        foreach ($progress['questions'] as $question) {
            $columns[] = TextColumn::make("graded_{$question['label']}")
                ->label("{$question['label']} Graded (of {$question['answered']})")
                ->tooltip($question['question'])
                ->getStateUsing(fn (array $record) => $record['grades'][$question['id']]);
        }

        return $table
            ->description("{$progress['total_applications']} applications to grade for {$application->year}. An application counts as graded once a host has saved any score for it.")
            ->records(fn () => $progress['hosts'])
            ->columns($columns)
            ->paginated(false)
            ->emptyStateHeading('No hosts found');
    }

    /**
     * Graded vs remaining applications, and grades per question, for every
     * host. Each non-empty essay answer is one essay to grade. An application counts
     * as graded once the host has saved any score for it, the same rule the grading
     * queue uses to skip it.
     */
    public function getHostProgress(): array
    {
        $application = $this->getApplication();

        // This year's essay questions; filter() keeps their form positions for numbering below
        $essayQuestions = collect($application?->form ?? [])
            ->filter(fn ($question) => ($question['type'] ?? null) === 'essay');
        $essayQuestionIds = $essayQuestions->pluck('id')->all();

        // Every essay to grade (a non-empty answer), keyed "applicantId|questionId" so it
        // works as a lookup set of applicant/question pairs
        $essays = AppAnswer::whereIn('question_id', $essayQuestionIds)
            ->whereNotNull('answer')
            ->where('answer', '!=', '')
            ->get(['applicant_id', 'question_id'])
            ->keyBy(fn ($answer) => $answer->applicant_id.'|'.$answer->question_id);

        // Applicants with at least one essay to grade, and how many answered each question
        $applicantIds = $essays->pluck('applicant_id')->unique()->values();
        $answeredCounts = $essays->countBy('question_id');

        // Numbered by form position, matching the Grading Overview columns
        $questions = $essayQuestions
            ->map(fn ($question, $index) => [
                'id' => $question['id'],
                'label' => 'Q'.($index + 1),
                'question' => $question['question'] ?? '',
                'answered' => $answeredCounts->get($question['id'], 0),
            ])
            ->values()
            ->all();

        // Every score on this year's essays, grouped by whoever gave it (not only hosts). Also matches
        // question_uuid like the other score queries do; nothing sets it, so scores match on question_id
        $scoresByScorer = AppScore::where(function ($query) use ($essayQuestionIds) {
            $query->whereIn('question_id', $essayQuestionIds)
                ->orWhereIn('question_uuid', $essayQuestionIds);
        })
            ->get(['applicant_id', 'scorer_id', 'question_id', 'question_uuid'])
            ->groupBy('scorer_id');

        // Only hosts grade, so moderators and admins are left out
        $hosts = User::where('role', 2)
            ->get()
            ->map(function ($host) use ($scoresByScorer, $essays, $applicantIds, $essayQuestionIds) {
                $scores = $scoresByScorer->get($host->id, collect());

                // Grades per question, counting each essay once and only essays that are up for grading.
                // Every question starts at 0 so the table can always look it up
                $gradesPerQuestion = array_fill_keys($essayQuestionIds, 0);
                $counted = [];
                foreach ($scores as $score) {
                    $questionId = in_array($score->question_id, $essayQuestionIds, true) ? $score->question_id : $score->question_uuid;
                    $key = $score->applicant_id.'|'.$questionId;

                    if (isset($counted[$key]) || ! $essays->has($key)) {
                        continue;
                    }

                    $counted[$key] = true;
                    $gradesPerQuestion[$questionId]++;
                }

                // Applications use a looser rule than essays: any score on this year's essays counts,
                // the same rule the grading queue uses to skip an applicant
                $gradedApplicationsCount = $scores->pluck('applicant_id')->unique()->intersect($applicantIds)->count();

                return [
                    'name' => $host->name ?? $host->reddit_user ?? ($host->anilist_id ? 'AniList #'.$host->anilist_id : 'User #'.$host->id),
                    'applications_graded' => $gradedApplicationsCount,
                    'applications_remaining' => $applicantIds->count() - $gradedApplicationsCount,
                    'grades' => $gradesPerQuestion,
                ];
            })
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();

        return [
            'total_applications' => $applicantIds->count(),
            'questions' => $questions,
            'hosts' => $hosts,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    $this->redirect(static::getUrl());
                }),
        ];
    }
}
