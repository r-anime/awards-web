<?php

namespace App\Filament\Admin\Resources\ApplicationResource\Pages;

use App\Filament\Admin\Resources\ApplicationResource;
use App\Filament\Admin\Pages\GradingPage;
use App\Models\Application;
use App\Models\AppScore;
use App\Models\AppAnswer;
use App\Models\User;
use Filament\Resources\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ApplicationGrading extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = ApplicationResource::class;

    protected string $view = 'filament.resources.application-resource.pages.application-grading';

    protected static ?string $title = 'Application Grading';

    protected static ?string $navigationLabel = 'Application Grading';

    protected static ?string $slug = 'application/grading';

    public function getApplication(): ?Application
    {
        $filterYear = session('selected-year-filter') ?? intval(app('current-year'));
        return Application::where('year', $filterYear)->first();
    }

    public function table(Table $table): Table
    {
        $application = $this->getApplication();

        if (!$application || !$application->form) {
            return $table->query(User::query()->whereRaw('1 = 0')); // Empty query
        }

        // Get all essay questions from the application form
        $essayQuestions = collect($application->form)->filter(function ($question) {
            return $question['type'] === 'essay';
        });

        $columns = [
            TextColumn::make('uuid')
                ->label('User UUID')
                ->sortable()
                ->searchable()
                ->formatStateUsing(function ($record) {
                    return $record->uuid ?? 'N/A';
                })
                ->url(function ($record) {
                    if ($record->uuid) {
                        return GradingPage::getUrlForUser($record->uuid);
                    }
                    return null;
                })
                ->color('primary')
                ->width('200px'),
        ];

        // Only show username column to role 3+, or to hosts when the option allows it
        if (GradingPage::canSeeApplicantNames()) {
            $columns[] = TextColumn::make('name')
                ->label('Username')
                ->sortable()
                ->searchable()
                ->getStateUsing(function ($record) {
                    return $record->name ?? $record->reddit_user ?? ($record->anilist_id ? 'AniList #' . $record->anilist_id : 'User #' . $record->id);
                })
                ->width('150px');
        }

        // Add answered and average score columns for each essay question
        $answeredColumns = [];
        $scoreColumns = [];
        foreach ($essayQuestions as $question) {
            $questionId = $question['id'];
            $questionText = $question['question'];
            $questionIndex = array_search($question, $essayQuestions->toArray()) + 1;

            $answeredColumns[] = IconColumn::make("answered_{$questionIndex}")
                ->label('Q' . $questionIndex)
                ->tooltip($questionText)
                ->boolean()
                ->getStateUsing(fn ($record) => $record->appAnswers->contains('question_id', $questionId))
                ->alignCenter();

            // This question's scores, from the batch loaded with the table query
            $questionScores = fn ($record) => $record->appScores->filter(
                fn ($score) => $score->question_id == $questionId || $score->question_uuid == $questionId
            );

            $scoreColumns[] = TextColumn::make("question_{$questionIndex}")
                ->label('Q' . $questionIndex)
                ->tooltip(function ($record) use ($questionScores, $questionText) {
                    $scores = $questionScores($record);

                    if ($scores->isEmpty()) {
                        return $questionText . "\n\n" . str_repeat("-", 50) . "\n\nNo grades assigned yet.";
                    }

                    $scoreList = [];
                    foreach ($scores as $score) {
                        // Show scorer name only for users with role 2 or higher
                        if (auth()->user()->role >= 2) {
                            $scorerName = $score->scorer ?
                                ($score->scorer->name ?? $score->scorer->reddit_user ?? ($score->scorer->anilist_id ? 'AniList #' . $score->scorer->anilist_id : 'User #' . $score->scorer->id)) :
                                'Unknown';
                        } else {
                            $scorerName = $score->scorer ?
                                'User #' . $score->scorer->id :
                                'Unknown';
                        }

                        $scoreList[] = $scorerName . ': ' . $score->score;
                    }

                    return $questionText . "\n\n" . str_repeat("-", 50) . "\n\nIndividual Scores:\n\n" . implode("\n\n", $scoreList);
                })
                ->getStateUsing(function ($record) use ($questionScores) {
                    $scores = $questionScores($record);

                    if ($scores->isEmpty()) {
                        return 'No grades';
                    }

                    $average = $scores->avg('score');
                    return number_format($average, 2);
                })
                // The column is computed, so sort by the same average in a subquery
                ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy(
                    AppScore::selectRaw('avg(score)')
                        ->whereColumn('app_scores.applicant_id', 'users.id')
                        ->where(function ($q) use ($questionId) {
                            $q->where('question_id', $questionId)
                              ->orWhere('question_uuid', $questionId);
                        }),
                    $direction
                ))
                ->width('100px');
        }

        if (!empty($scoreColumns)) {
            $columns[] = ColumnGroup::make('Answered', $answeredColumns);
            $columns[] = ColumnGroup::make('Average Score', $scoreColumns);
        }

        // Debug: Let's see what users we're getting
        $essayQuestionIds = collect($application->form)
            ->filter(fn($q) => $q['type'] === 'essay')
            ->pluck('id')
            ->toArray();

        $userQuery = User::query()
            ->with(['appAnswers' => function ($query) use ($essayQuestionIds) {
                // Only what the Answered columns need, not the essay text
                $query->select('applicant_id', 'question_id')
                      ->whereIn('question_id', $essayQuestionIds)
                      ->whereNotNull('answer')
                      ->where('answer', '!=', '');
            }])
            // Scores and scorers for the score columns and their tooltips, one query each per page
            ->with(['appScores' => function ($query) use ($essayQuestionIds) {
                $query->where(function ($q) use ($essayQuestionIds) {
                    $q->whereIn('question_id', $essayQuestionIds)
                      ->orWhereIn('question_uuid', $essayQuestionIds);
                });
            }, 'appScores.scorer'])
            ->whereHas('appAnswers', function (Builder $query) use ($essayQuestionIds) {
                $query->whereIn('question_id', $essayQuestionIds)
                      ->whereNotNull('answer')
                      ->where('answer', '!=', '');
            });

        return $table
            ->query($userQuery)
            ->columns($columns)
            ->filters([
                SelectFilter::make('year')
                    ->label('Year')
                    ->options(function () {
                        $years = Application::distinct()->pluck('year', 'year')->toArray();
                        return array_combine($years, $years);
                    })
                    ->default(session('selected-year-filter') ?? app('current-year'))
                    ->query(function (Builder $query, array $data): Builder {
                        if (!empty($data['value'])) {
                            session(['selected-year-filter' => $data['value']]);
                        }
                        return $query;
                    }),
            ])
            ->defaultSort('id');
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    $this->redirect(static::getUrl());
                }),
        ];
    }
}
