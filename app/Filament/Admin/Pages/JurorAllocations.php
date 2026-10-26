<?php

namespace App\Filament\Admin\Pages;

use App\Models\Application;
use App\Models\AppScore;
use App\Models\Category;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JurorAllocations extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected string $view = 'filament.admin.pages.juror-allocations';

    protected static ?string $title = 'Juror Allocations';

    protected static ?string $navigationLabel = 'Juror Allocations';

    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'juror-allocations';

    protected static bool $shouldRegisterNavigation = false;

    public $baseCutoff = 1.5;

    public $mainCategoryCutoff = 2.5;

    protected $listeners = ['refreshData' => 'refreshData'];

    public function refreshData()
    {
        $this->clearCache();
        // Don't redirect, just clear cache to force data refresh
    }

    public static function canAccess(): bool
    {
        return auth()->check() && GradingPage::canSeeApplicantNames();
    }

    public function table(Table $table): Table
    {
        $columns = [
            TextColumn::make('name')
                ->label('Applicant')
                ->searchable(),
            TextColumn::make('total_average_score')
                ->label('Total Avg')
                ->formatStateUsing(fn ($state) => $state > 0 ? $state : 'N/A')
                ->sortable(),
        ];

        foreach ($this->getEssayQuestionColumns() as $question) {
            $columns[] = TextColumn::make("score_{$question['label']}")
                ->label($question['label'])
                ->headerTooltip($question['question'])
                ->sortable();
        }

        $columns[] = TextColumn::make('allocated_to')
            ->label('Allocated To')
            ->badge()
            ->color('success')
            ->listWithLineBreaks()
            ->placeholder('None');
        $columns[] = TextColumn::make('main_preferences')
            ->label('Main Preferences')
            ->badge()
            ->color('info')
            ->listWithLineBreaks()
            ->placeholder('None');
        $columns[] = TextColumn::make('unwanted_main_preferences')
            ->label("Doesn't Want")
            ->badge()
            ->color('danger')
            ->listWithLineBreaks()
            ->placeholder('None');
        $columns[] = TextColumn::make('non_main_preferences')
            ->label('Non-Main Preferences')
            ->badge()
            ->color('gray')
            ->listWithLineBreaks()
            ->placeholder('None');

        $categoryOptions = Category::where('year', $this->getApplication()?->year)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
        $allocationOptions = collect($this->getMainCategories())
            ->pluck('name', 'id')
            ->put('unallocated', 'Unallocated')
            ->all();

        return $table
            // Evaluated on every render, after cutoff changes, so the rows always match the inputs
            ->records(fn (?string $search, ?string $sortColumn, ?string $sortDirection, ?array $filters) => $this->filterAndSortRows(
                collect($this->getTableRows()),
                $search,
                $sortColumn,
                $sortDirection,
                $filters ?? [],
            ))
            ->columns($columns)
            ->filters([
                SelectFilter::make('allocated_to')
                    ->label('Allocated To')
                    ->options($allocationOptions),
                SelectFilter::make('prefers')
                    ->label('Prefers Category')
                    ->options($categoryOptions),
            ])
            ->paginated(false)
            ->emptyStateHeading('No applicants found');
    }

    /**
     * Applies the table's search, sort and filters to the rows. Filament leaves this to
     * the page when the table is built from records() instead of a query.
     */
    private function filterAndSortRows(Collection $rows, ?string $search, ?string $sortColumn, ?string $sortDirection, array $filters): Collection
    {
        // search
        if (filled($search)) {
            $rows = $rows->filter(fn ($row) => Str::contains($row['name'], $search, ignoreCase: true));
        }

        // allocated to
        $allocatedTo = $filters['allocated_to']['value'] ?? null;
        if (filled($allocatedTo)) {
            if ($allocatedTo === 'unallocated') {
                $rows = $rows->filter(fn ($row) => empty($row['allocated_category_ids']));
            } else {
                $rows = $rows->filter(fn ($row) => in_array($allocatedTo, $row['allocated_category_ids']));
            }
        }

        // preference
        $prefers = $filters['prefers']['value'] ?? null;
        if ($prefers) {
            $rows = $rows->filter(fn ($row) => in_array($prefers, $row['main_preference_ids'])
                || in_array($prefers, $row['non_main_preference_ids']));
        }

        // sort
        if (filled($sortColumn) && filled($sortDirection)) {
            $rows = $rows->sortBy($sortColumn, descending: $sortDirection === 'desc');
        }

        return $rows;
    }

    /**
     * One flat row per applicant for the table: scores keyed by question label, and
     * category IDs alongside the names so filters can match on them. Keyed by applicant ID.
     */
    public function getTableRows(): array
    {
        $applicants = $this->getEligibleApplicants();
        $questions = $this->getEssayQuestionColumns();
        $allocatedTo = $this->allocatedCategoryNames();

        return collect($applicants)
            ->map(function ($applicant) use ($questions, $allocatedTo) {
                $row = [
                    'id' => $applicant['id'],
                    'name' => $applicant['name'],
                    'total_average_score' => $applicant['total_average_score'],
                ];

                foreach ($questions as $question) {
                    $row["score_{$question['label']}"] = $applicant['individual_scores'][$question['id']]['average_score'] ?? null;
                }

                $row['allocated_to'] = array_values($allocatedTo[$applicant['id']] ?? []);
                $row['allocated_category_ids'] = array_keys($allocatedTo[$applicant['id']] ?? []);
                $row['main_preferences'] = collect($applicant['main_preferences'])
                    ->values()
                    ->map(fn ($preference, $index) => ($index + 1).'. '.$preference['name'])
                    ->all();
                $row['main_preference_ids'] = array_column($applicant['main_preferences'], 'id');
                $row['unwanted_main_preferences'] = array_column($applicant['unwanted_main_preferences'], 'name');
                $row['non_main_preferences'] = array_column($applicant['non_main_preferences'], 'name');
                $row['non_main_preference_ids'] = array_column($applicant['non_main_preferences'], 'id');

                return $row;
            })
            ->keyBy('id')
            ->all();
    }

    /**
     * Categories each eligible applicant is allocated to, as [applicant ID => [category ID => name]]
     */
    private function allocatedCategoryNames(): array
    {
        $allocatedTo = [];
        foreach ($this->currentAllocations()['categories'] as $categoryId => $category) {
            foreach ($category['allocations'] as $applicant) {
                $allocatedTo[$applicant['id']][$categoryId] = $category['name'];
            }
        }

        return $allocatedTo;
    }

    /**
     * Everything the page knows about every applicant who answered the preference question,
     * including those below the base cutoff, for allocating by hand in a spreadsheet. Eligibility
     * and the current allocations (at the page's cutoffs) are columns, so the sheet can filter on them.
     */
    public function exportCsv(): StreamedResponse
    {
        $application = $this->getApplication();
        $applicants = $application && $application->form ? $this->getAllApplicantsWithScores($application) : [];
        $questions = $this->getEssayQuestionColumns();
        $allocatedTo = $this->allocatedCategoryNames();
        $baseCutoff = (float) $this->baseCutoff;

        // One column per main preference rank, as many as the longest list
        $mainPreferenceCount = collect($applicants)->max(fn ($applicant) => count($applicant['main_preferences'])) ?? 0;

        $header = ['User ID', 'UUID', 'Applicant', 'Total Avg'];
        foreach ($questions as $question) {
            $header[] = $question['label'];
            $header[] = "{$question['label']} Grades";
        }
        array_push($header, 'Eligible', 'Above Base Cutoff', 'Yes to Both', 'Allocated To');
        for ($rank = 1; $rank <= $mainPreferenceCount; $rank++) {
            $header[] = "Main Preference {$rank}";
        }
        array_push($header, "Doesn't Want", 'Non-Main Preferences');

        $filename = 'juror-allocations-'.now()->toDateString().'.csv';

        return response()->streamDownload(function () use ($applicants, $questions, $allocatedTo, $baseCutoff, $mainPreferenceCount, $header) {
            $output = fopen('php://output', 'w');

            // Byte order mark, so Excel reads non-ASCII names as UTF-8
            fwrite($output, "\xEF\xBB\xBF");
            // CRLF row endings, as the CSV standard (RFC 4180) specifies
            fputcsv($output, $header, escape: '', eol: "\r\n");

            foreach ($applicants as $applicant) {
                $row = [$applicant['id'], $applicant['uuid'], $applicant['name'], $applicant['total_average_score']];

                // Average and number of grades, so a 0 that's "not graded yet" can be told apart
                foreach ($questions as $question) {
                    $scores = $applicant['individual_scores'][$question['id']] ?? null;
                    $row[] = $scores['average_score'] ?? '';
                    $row[] = $scores['total_scores'] ?? 0;
                }

                $row[] = $this->isEligible($applicant, $baseCutoff) ? 'Yes' : 'No';
                $row[] = $this->isAboveBaseCutoff($applicant, $baseCutoff) ? 'Yes' : 'No';
                $row[] = $applicant['answered_yes_to_both'] ? 'Yes' : 'No';
                $row[] = implode('; ', $allocatedTo[$applicant['id']] ?? []);

                $mainPreferences = array_column($applicant['main_preferences'], 'name');
                for ($index = 0; $index < $mainPreferenceCount; $index++) {
                    $row[] = $mainPreferences[$index] ?? '';
                }

                $row[] = implode('; ', array_column($applicant['unwanted_main_preferences'], 'name'));
                $row[] = implode('; ', array_column($applicant['non_main_preferences'], 'name'));

                fputcsv($output, array_map(fn ($value) => $this->csvCell($value), $row), escape: '', eol: "\r\n");
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Stops spreadsheet apps from running text such as "=HYPERLINK(...)" as a formula
     */
    private function csvCell($value)
    {
        return is_string($value) && preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }

    /**
     * This year's essay questions, labelled by form position like Grading Overview and Host Progress
     */
    public function getEssayQuestionColumns(): array
    {
        return collect($this->getApplication()?->form ?? [])
            ->filter(fn ($question) => ($question['type'] ?? null) === 'essay')
            ->map(fn ($question, $index) => [
                'id' => $question['id'],
                'label' => 'Q'.($index + 1),
                'question' => $question['question'] ?? '',
            ])
            ->values()
            ->all();
    }

    /**
     * Categories jurors are allocated to: "main" ones plus OP/ED, without Comedy
     */
    public function getMainCategories(): array
    {
        $application = $this->getApplication();

        if (! $application) {
            return [];
        }

        // Get all categories with type "main" OR OP/ED categories for this application's year, comedy getting fucked up lmao
        return once(function () use ($application) {
            return Category::where('year', $application->year)
                ->where(function ($query) {
                    $query->where('type', 'main')
                        ->orWhere('name', 'LIKE', '%OP%')
                        ->orWhere('name', 'LIKE', '%ED%');
                })
                ->where('name', 'NOT LIKE', '%comedy%')
                ->where('name', 'NOT LIKE', '%Comedy%')
                // The admin-set order, like the rest of the site, with id breaking ties
                ->orderBy('order')
                ->orderBy('id')
                ->get()
                ->map(fn ($category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'max_jurors' => 15,
                ])
                ->all();
        });
    }

    /**
     * The allocations at the page's current cutoffs, worked out once per request for each pair
     * of cutoff values. The cutoffs are read here and passed in, so the calculation below never
     * reads them from the page itself, and once() tells different values apart.
     */
    public function currentAllocations(): array
    {
        $baseCutoff = (float) $this->baseCutoff;
        $mainCutoff = (float) $this->mainCategoryCutoff;

        return once(function () use ($baseCutoff, $mainCutoff) {
            return $this->getAllocations($this->eligibleApplicants($baseCutoff), $mainCutoff);
        });
    }

    /**
     * Allocates applicants to the main categories. Returns each category's allocations,
     * keyed by category ID, and the applicants left unallocated.
     *
     * @param  float  $mainCutoff  minimum total (or Q2 score for OP/ED) to be allocated
     */
    public function getAllocations(array $applicants, float $mainCutoff): array
    {
        $allocations = [];
        $remainingApplicants = $applicants;
        $vaxxinatedApplicants = [];
        $jurorAllocationCount = []; // Track how many categories each juror is allocated to
        $allocatedJurorIds = [];      // Track allocated juror IDs to find completely unallocated applicants

        $OPED_MAX = 11;

        foreach ($this->getMainCategories() as $category) {
            $categoryAllocations = [];
            $categoryId = $category['id'];
            $categoryName = $category['name'];
            $maxJurors = $category['max_jurors'];

            // Check if this is OP or ED category
            $isOPorED = (stripos($categoryName, 'OP') !== false || stripos($categoryName, 'ED') !== false);

            if ($isOPorED) {
                // For OP/ED: up to 11 jurors who scored above main threshold only on question 2
                $allocatedCount = 0;
                foreach ($remainingApplicants as $applicant) {
                    if ($allocatedCount >= $OPED_MAX) {
                        break; // Stop at $OPED_MAX jurors for OP/ED categories
                    }

                    $jurorId = $applicant['id'];
                    $currentAllocations = $jurorAllocationCount[$jurorId] ?? 0;

                    // Check if they have preference for this category in non-main preferences
                    $hasPreference = false;
                    foreach ($applicant['non_main_preferences'] as $pref) {
                        if ($pref['id'] == $categoryId) {
                            $hasPreference = true;
                            break;
                        }
                    }

                    if (! $hasPreference) {
                        continue; // Skip if no preference for this category
                    }

                    // Find the average_score for the 2nd question in the JSON structure
                    $question2Score = 0;
                    $questionCount = 0;
                    foreach ($applicant['individual_scores'] as $questionId => $scoreData) {
                        $questionCount++;
                        if ($questionCount == 2) { // 2nd question
                            $question2Score = $scoreData['average_score'] ?? 0;
                            break;
                        }
                    }

                    if ($question2Score >= $mainCutoff) {
                        $categoryAllocations[] = $applicant;
                        $allocatedCount++;
                    }
                }
            } else {
                // Regular category logic
                // Sort applicants by preference order for this category
                $preferredApplicants = [];
                foreach ($remainingApplicants as $applicant) {
                    // No limit on number of categories per juror
                    $jurorId = $applicant['id'];
                    $currentAllocations = $jurorAllocationCount[$jurorId] ?? 0;

                    foreach ($applicant['main_preferences'] as $pref) {
                        if ($pref['id'] == $categoryId) {
                            $preferredApplicants[] = [
                                'applicant' => $applicant,
                                'preference_order' => $pref['order'] ?? 999,
                                'score' => $applicant['total_average_score'],
                            ];
                            break;
                        }
                    }
                }

                // Sort by preference order, then by score
                usort($preferredApplicants, function ($a, $b) {
                    if ($a['preference_order'] == $b['preference_order']) {
                        return $b['score'] <=> $a['score']; // Higher score first
                    }

                    return $a['preference_order'] <=> $b['preference_order'];
                });

                // Allocate up to max_jurors
                $allocatedCount = 0;
                $lastPreferenceOrder = null;

                foreach ($preferredApplicants as $prefApplicant) {
                    if ($allocatedCount >= $maxJurors) {
                        // Check if this applicant has the same preference order as the last allocated
                        if ($lastPreferenceOrder !== null && $prefApplicant['preference_order'] == $lastPreferenceOrder) {
                            $vaxxinatedApplicants[] = $prefApplicant['applicant'];
                        }
                        break;
                    }

                    // Check if applicant meets main category cutoff
                    if ($prefApplicant['score'] >= $mainCutoff) {
                        $categoryAllocations[] = $prefApplicant['applicant'];

                        // Track allocation count for this juror
                        $jurorId = $prefApplicant['applicant']['id'];
                        $jurorAllocationCount[$jurorId] = ($jurorAllocationCount[$jurorId] ?? 0) + 1;

                        $allocatedCount++;
                        $lastPreferenceOrder = $prefApplicant['preference_order'];
                    }
                }
            }

            $allocations[$categoryId] = [
                'name' => $category['name'],
                'allocations' => $categoryAllocations,
                'count' => count($categoryAllocations),
                'max_jurors' => $maxJurors,
            ];

            $allocatedJurorIds += array_fill_keys(
                array_column($categoryAllocations, 'id'),
                true
            );

        }

        $remainingApplicants = array_filter(
            $remainingApplicants,
            fn ($applicant) => ! isset($allocatedJurorIds[$applicant['id']])
        );

        return [
            'categories' => $allocations,
            'unallocated' => $remainingApplicants,
        ];
    }

    public function getApplication(): ?Application
    {
        $filterYear = session('selected-year-filter') ?? intval(app('current-year'));

        return once(function () use ($filterYear) {
            return Application::where('year', $filterYear)->first();
        });
    }

    // Applicant list with scores, at the page's current base cutoff
    public function getEligibleApplicants(): array
    {
        return $this->eligibleApplicants((float) $this->baseCutoff);
    }

    // Applicant list with scores, base cutoff applied. Worked out once per request for each cutoff
    private function eligibleApplicants(float $baseCutoff): array
    {
        $application = $this->getApplication();

        // Return empty if no application form
        if (! $application || ! $application->form) {
            return [];
        }

        return once(function () use ($application, $baseCutoff) {
            $applicants = $this->getAllApplicantsWithScores($application);

            return array_values($this->applyCutoffFilter($applicants, $baseCutoff));
        });
    }

    // Queried applicant list with scores
    // Cached before base cutoff since it can change on every request
    private function getAllApplicantsWithScores(Application $application): array
    {
        // Check cache first
        $cacheKey = 'juror_allocations_'.$application->year.'_'.$application->updated_at->timestamp;
        $cachedData = cache()->get($cacheKey);
        if ($cachedData !== null) {
            return $cachedData;
        }

        // Set execution time limit
        set_time_limit(60);

        $applicationForm = collect($application->form);

        // 3 filters; negligible performance hit on <10 questions

        $multipleChoiceQuestions = $applicationForm->filter(function ($question) {
            return $question['type'] === 'multiple_choice';
        });

        $essayQuestions = $applicationForm->filter(function ($question) {
            return $question['type'] === 'essay';
        });

        // Get preference question
        $preferenceQuestion = $applicationForm->first(function ($question) {
            return $question['type'] === 'preference';
        });

        $multipleChoiceQuestionIds = $multipleChoiceQuestions->pluck('id')->toArray();
        $essayQuestionIds = $essayQuestions->pluck('id')->toArray();
        $preferenceQuestionId = $preferenceQuestion['id'] ?? null;

        // Build map of MCQ question IDs => Yes option IDs (questions without a Yes option are skipped).
        // The form builder always saves option text and an ID, so only a missing options list is guarded
        $yesOptionUuids = [];
        foreach ($multipleChoiceQuestions as $question) {
            foreach ($question['options'] ?? [] as $option) {
                if (strtolower(trim($option['option'])) === 'yes') {
                    $yesOptionUuids[$question['id']] = $option['id'];
                    break;
                }
            }
        }

        // Load chunked

        $chunkSize = 50;
        $allApplicantsData = [];

        // Pre-load categories for preferences
        $categories = Category::where('year', $application->year)->get()->keyBy('id');

        // TODO: filter answers by current application
        User::whereHas('appAnswers', function ($query) use ($preferenceQuestionId) {
            // Include all applicants who have any answers (not just essays)
            $query->whereNotNull('answer')
                ->where('answer', '!=', '');
            if ($preferenceQuestionId) {
                $query->where('question_id', $preferenceQuestionId);
            }
        })->chunk($chunkSize, function ($applicants) use ($essayQuestions, $essayQuestionIds, $multipleChoiceQuestionIds, $yesOptionUuids, $preferenceQuestionId, $categories, &$allApplicantsData) {
            // Pre-load scores for this chunk
            $allScores = AppScore::whereIn('applicant_id', $applicants->pluck('id'))
                ->where(function ($query) use ($essayQuestionIds) {
                    $query->whereIn('question_id', $essayQuestionIds)
                        ->orWhereIn('question_uuid', $essayQuestionIds);
                })
                ->get()
                ->groupBy('applicant_id');

            // Load answers for this chunk
            $applicants->load(['appAnswers' => function ($query) use ($essayQuestionIds, $multipleChoiceQuestionIds, $preferenceQuestionId) {
                $query->whereIn('question_id', array_merge($multipleChoiceQuestionIds, $essayQuestionIds, $preferenceQuestionId ? [$preferenceQuestionId] : []))
                    ->whereNotNull('answer')
                    ->where('answer', '!=', '');
            }]);

            $chunkData = $this->processApplicantsData($applicants, $essayQuestions, $allScores, $yesOptionUuids, $preferenceQuestionId, $categories);
            $allApplicantsData = array_merge($allApplicantsData, $chunkData);
        });

        // ! Cutoff filtering moved out of cache to getEligibleApplicants

        // Sort by total average score (descending)
        usort($allApplicantsData, function ($a, $b) {
            return $b['total_average_score'] <=> $a['total_average_score'];
        });

        // Cache the results for 5 minutes
        cache()->put($cacheKey, $allApplicantsData, 300);

        return $allApplicantsData;
    }

    /**
     * @param  EloquentCollection<int, User>  $applicants  one chunk, with appAnswers loaded
     * @param  Collection<int, array>  $essayQuestions  essay questions from the form JSON
     * @param  Collection<int, EloquentCollection<int, AppScore>>  $allScores  keyed by applicant ID
     * @param  array<string, string>  $yesOptionUuids  multiple-choice question ID => Yes option ID
     * @param  EloquentCollection<int, Category>  $categories  this year's categories, keyed by ID
     */
    private function processApplicantsData(EloquentCollection $applicants, Collection $essayQuestions, Collection $allScores, array $yesOptionUuids, ?string $preferenceQuestionId, EloquentCollection $categories): array
    {
        $applicantsData = [];

        foreach ($applicants as $applicant) {
            $applicantData = [
                'id' => $applicant->id,
                'uuid' => $applicant->uuid,
                'name' => $applicant->name ?? $applicant->reddit_user ?? 'User #'.$applicant->id,
                'individual_scores' => [],
                'total_average_score' => 0,
                'main_preferences' => [],
                'non_main_preferences' => [],
                'unwanted_main_preferences' => [],
                'answered_yes_to_both' => false,
            ];

            // This applicant's answers as question ID => answer: essay text, the chosen option's ID
            // (multiple choice), or the preference JSON
            $answers = $applicant->appAnswers->pluck('answer', 'question_id');

            // Get scores for this applicant
            $applicantScores = $allScores->get($applicant->id, collect());

            // Calculate individual question scores
            $questionAverages = []; // Is this working correctly? What is this tracking?
            foreach ($essayQuestions as $question) {
                $questionId = $question['id'];
                $scores = $applicantScores->filter(function ($score) use ($questionId) {
                    return $score->question_id == $questionId || $score->question_uuid == $questionId;
                });

                if ($scores->isNotEmpty()) {
                    $average = $scores->avg('score');
                    $questionAverages[] = $average;
                    $applicantData['individual_scores'][$questionId] = [
                        'question_text' => $question['question'],
                        'average_score' => round($average, 2),
                        'total_scores' => $scores->count(),
                    ];
                } else {
                    // Give applicants without essays a score of 0
                    $questionAverages[] = 0;
                    $applicantData['individual_scores'][$questionId] = [
                        'question_text' => $question['question'],
                        'average_score' => 0,
                        'total_scores' => 0,
                    ];
                }
            }

            // Calculate total average score (always calculate, even if all scores are 0)
            $applicantData['total_average_score'] = round(array_sum($questionAverages) / count($questionAverages), 2);

            // Get preferences
            if ($preferenceQuestionId) {
                $preferenceAnswer = $answers->get($preferenceQuestionId);

                if ($preferenceAnswer) {
                    $preferenceData = json_decode($preferenceAnswer, true);

                    if ($preferenceData) {
                        // Parse main preferences (ordered list)
                        if (isset($preferenceData['main_order']) && $preferenceData['main_order']) {
                            $mainOrderData = json_decode($preferenceData['main_order'], true);
                            if (is_array($mainOrderData)) {
                                $applicantData['main_preferences'] = $this->formatPreferences($mainOrderData, $categories);
                            }
                        }

                        // Parse main exclusions
                        if (isset($preferenceData['no_main_order']) && $preferenceData['no_main_order']) {
                            $noMainData = json_decode($preferenceData['no_main_order'], true);
                            if (is_array($noMainData)) {
                                $applicantData['unwanted_main_preferences'] = $this->formatPreferences($noMainData, $categories);
                            }
                        }

                        // Parse non-main preferences
                        if (isset($preferenceData['non_main']) && is_array($preferenceData['non_main'])) {
                            $applicantData['non_main_preferences'] = $this->formatPreferences($preferenceData['non_main'], $categories);
                        }
                    }
                }
            }

            // Count and flag whether both mcqs have been answered 'yes' to
            $yesCount = 0;
            foreach ($yesOptionUuids as $questionId => $yesOptionUuid) {
                if ($answers->get($questionId) === $yesOptionUuid) {
                    $yesCount++;
                }
            }

            $applicantData['answered_yes_to_both'] = $yesCount >= 2;

            $applicantsData[] = $applicantData;
        }

        return $applicantsData;
    }

    private function formatPreferences(array $preferences, $categories): array
    {
        $formatted = [];

        foreach ($preferences as $preference) {
            \Log::info('Raw preference type and value:', ['type' => gettype($preference), 'value' => $preference]);

            // Handle nested preference format: {"preference":{"category_id":"230","order":1}}
            // First check if it's a string that needs JSON decoding
            if (is_string($preference)) {
                $decodedPreference = json_decode($preference, true);
                \Log::info('Decoded string preference:', ['original' => $preference, 'decoded' => $decodedPreference]);
                if ($decodedPreference && is_array($decodedPreference)) {
                    $preference = $decodedPreference;
                }
            }

            // Check for direct category_id format: {"category_id":"230","order":3}
            if (is_array($preference) && isset($preference['category_id'])) {
                \Log::info('Processing direct category_id preference:', $preference);
                $categoryId = $preference['category_id'];
                $order = $preference['order'] ?? null;

                if ($categoryId) {
                    $category = $categories->get($categoryId);
                    if ($category) {
                        $formatted[] = [
                            'id' => $categoryId,
                            'name' => $category->name,
                            'order' => $order,
                        ];
                        \Log::info('Successfully processed preference:', ['category_id' => $categoryId, 'name' => $category->name, 'order' => $order]);
                    } else {
                        \Log::warning('Category not found for category_id: '.$categoryId);
                    }
                }
            }
            // Check for nested structure: {"preference":{"category_id":"230","order":3}}
            elseif (is_array($preference) && isset($preference['preference'])) {
                \Log::info('Processing nested preference:', $preference);
                $prefData = $preference['preference'];
                $categoryId = $prefData['category_id'] ?? null;
                $order = $prefData['order'] ?? null;

                if ($categoryId) {
                    $category = $categories->get($categoryId);
                    if ($category) {
                        $formatted[] = [
                            'id' => $categoryId,
                            'name' => $category->name,
                            'order' => $order,
                        ];
                        \Log::info('Successfully processed nested preference:', ['category_id' => $categoryId, 'name' => $category->name, 'order' => $order]);
                    } else {
                        \Log::warning('Category not found for nested ID: '.$categoryId);
                    }
                }
            }
            // Handle direct format: {"id":"230","order":1}
            elseif (is_array($preference) && isset($preference['id'])) {
                $categoryId = $preference['id'];
                $category = $categories->get($categoryId);

                if ($category) {
                    $formatted[] = [
                        'id' => $categoryId,
                        'name' => $category->name,
                        'order' => $preference['order'] ?? null,
                    ];
                } else {
                    \Log::warning('Category not found for ID: '.$categoryId);
                }
            }
            // Handle simple numeric IDs
            elseif (is_numeric($preference)) {
                $category = $categories->get($preference);
                if ($category) {
                    $formatted[] = [
                        'id' => $preference,
                        'name' => $category->name,
                        'order' => null,
                    ];
                } else {
                    \Log::warning('Category not found for numeric ID: '.$preference);
                }
            } else {
                \Log::warning('Unexpected preference format:', ['preference' => $preference]);
            }
        }

        return $formatted;
    }

    private function applyCutoffFilter(array $applicantsData, float $baseCutoff): array
    {
        return array_filter($applicantsData, fn ($applicant) => $this->isEligible($applicant, $baseCutoff));
    }

    // Shown on the page: above the base cutoff, or kept by answering Yes to both choices
    private function isEligible(array $applicant, float $baseCutoff): bool
    {
        return $this->isAboveBaseCutoff($applicant, $baseCutoff) || $applicant['answered_yes_to_both'];
    }

    private function isAboveBaseCutoff(array $applicant, float $baseCutoff): bool
    {
        return $applicant['total_average_score'] >= $baseCutoff;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export-csv')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn () => $this->exportCsv()),
            Action::make('update')
                ->label('Update')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    $this->dispatch('refreshData');
                }),
            Action::make('clear-cache')
                ->label('Clear Cache')
                ->icon('heroicon-o-trash')
                ->color('warning')
                ->action(function () {
                    $this->clearCache();
                    $this->redirect(static::getUrl());
                }),
        ];
    }

    private function clearCache(): void
    {
        $application = $this->getApplication();
        if ($application) {
            $cacheKey = 'juror_allocations_'.$application->year.'_'.$application->updated_at->timestamp;
            Cache::forget($cacheKey);
        }
    }

    private function debugPreferences(): void
    {
        $application = $this->getApplication();
        if (! $application || ! $application->form) {
            \Log::info('No application found for debugging');

            return;
        }

        // Get preference question
        $preferenceQuestion = collect($application->form)->first(function ($question) {
            return $question['type'] === 'preference';
        });

        if (! $preferenceQuestion) {
            \Log::info('No preference question found');

            return;
        }

        \Log::info('Preference question found:', $preferenceQuestion);

        // Get a sample applicant with preferences
        $sampleApplicant = User::whereHas('appAnswers', function ($query) use ($preferenceQuestion) {
            $query->where('question_id', $preferenceQuestion['id'])
                ->whereNotNull('answer')
                ->where('answer', '!=', '');
        })->with(['appAnswers' => function ($query) use ($preferenceQuestion) {
            $query->where('question_id', $preferenceQuestion['id']);
        }])->first();

        if ($sampleApplicant) {
            \Log::info('Sample applicant found:', [
                'id' => $sampleApplicant->id,
                'name' => $sampleApplicant->name ?? $sampleApplicant->reddit_user,
                'preference_answer' => $sampleApplicant->appAnswers->first()->answer ?? 'No answer',
            ]);
        } else {
            \Log::info('No applicants with preferences found');
        }

        // Get categories
        $categories = Category::where('year', $application->year)->get();
        \Log::info('Available categories:', $categories->pluck('name', 'id')->toArray());
    }
}
