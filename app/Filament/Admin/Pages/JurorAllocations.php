<?php

namespace App\Filament\Admin\Pages;

use App\Models\Application;
use App\Models\AppAnswer;
use App\Models\AppScore;
use App\Models\User;
use App\Models\Category;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class JurorAllocations extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';
    
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
        return auth()->check() && auth()->user()->role >= 2;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Cutoff Settings')
                    ->schema([
                             TextInput::make('baseCutoff')
                                 ->label('Base Cutoff')
                                 ->numeric()
                                 ->default(1.5)
                                 ->step(0.1)
                                 ->minValue(0)
                                 ->maxValue(10),
                             TextInput::make('mainCategoryCutoff')
                                 ->label('Main Category Cutoff')
                                 ->numeric()
                                 ->default(2.5)
                                 ->step(0.1)
                                 ->minValue(0)
                                 ->maxValue(10),
                    ])
                    ->columns(2)
            ])
            ->statePath('data');
    }

    public function getApplication(): ?Application
    {
        $filterYear = session('selected-year-filter') ?? intval(app('current-year'));
        return Application::where('year', $filterYear)->first();
    }

    public function getApplicantsWithScores(): array
    {
        $application = $this->getApplication();
        
        if (!$application || !$application->form) {
            return [];
        }

        // Check cache first
        $cacheKey = 'juror_allocations_' . $application->year . '_' . $application->updated_at->timestamp;
        $cachedData = cache()->get($cacheKey);
        if ($cachedData !== null) {
            return $cachedData;
        }

        // Set execution time limit
        set_time_limit(60);

        // Get all essay questions from the application form
        $essayQuestions = collect($application->form)->filter(function ($question) {
            return $question['type'] === 'essay';
        });

        // Get preference question
        $preferenceQuestion = collect($application->form)->first(function ($question) {
            return $question['type'] === 'preference';
        });

        $essayQuestionIds = $essayQuestions->pluck('id')->toArray();
        $preferenceQuestionId = $preferenceQuestion['id'] ?? null;

        // Get applicant count first to determine if we need chunking
        // Include all applicants who have any answers (not just essays)
        $applicantCount = User::whereHas('appAnswers', function ($query) use ($preferenceQuestionId) {
            $query->whereNotNull('answer')
                  ->where('answer', '!=', '');
            if ($preferenceQuestionId) {
                $query->where('question_id', $preferenceQuestionId);
            }
        })->count();

        // If too many applicants, process in chunks
        if ($applicantCount > 100) {
            return $this->getApplicantsWithScoresChunked($essayQuestions, $preferenceQuestion, $essayQuestionIds, $preferenceQuestionId, $application, $cacheKey);
        }

        // Optimized query: Get applicants with their answers and scores in one go
        $applicants = User::with([
            'appAnswers' => function ($query) use ($essayQuestionIds, $preferenceQuestionId) {
                $query->whereIn('question_id', array_merge($essayQuestionIds, $preferenceQuestionId ? [$preferenceQuestionId] : []))
                      ->whereNotNull('answer')
                      ->where('answer', '!=', '');
            }
        ])->whereHas('appAnswers', function ($query) use ($preferenceQuestionId) {
            // Include all applicants who have any answers (not just essays)
            $query->whereNotNull('answer')
                  ->where('answer', '!=', '');
            if ($preferenceQuestionId) {
                $query->where('question_id', $preferenceQuestionId);
            }
        })->get();

        // Pre-load all scores for all applicants in one query
        $allScores = AppScore::whereIn('applicant_id', $applicants->pluck('id'))
            ->where(function ($query) use ($essayQuestionIds) {
                $query->whereIn('question_id', $essayQuestionIds)
                      ->orWhereIn('question_uuid', $essayQuestionIds);
            })
            ->get()
            ->groupBy('applicant_id');

        // Pre-load categories for preferences
        $categories = Category::where('year', $application->year)->get()->keyBy('id');

        $applicantsData = $this->processApplicantsData($applicants, $essayQuestions, $allScores, $preferenceQuestionId, $categories);

        // Apply cutoff filtering
        $applicantsData = $this->applyCutoffFilter($applicantsData, $application);

        // Sort by total average score (descending)
        usort($applicantsData, function ($a, $b) {
            return $b['total_average_score'] <=> $a['total_average_score'];
        });

        // Cache the results for 5 minutes
        cache()->put($cacheKey, $applicantsData, 300);

        return $applicantsData;
    }

    private function getApplicantsWithScoresChunked($essayQuestions, $preferenceQuestion, $essayQuestionIds, $preferenceQuestionId, $application, $cacheKey): array
    {
        $chunkSize = 50;
        $allApplicantsData = [];
        
        // Pre-load categories for preferences
        $categories = Category::where('year', $application->year)->get()->keyBy('id');

        User::whereHas('appAnswers', function ($query) use ($preferenceQuestionId) {
            // Include all applicants who have any answers (not just essays)
            $query->whereNotNull('answer')
                  ->where('answer', '!=', '');
            if ($preferenceQuestionId) {
                $query->where('question_id', $preferenceQuestionId);
            }
        })->chunk($chunkSize, function ($applicants) use ($essayQuestions, $essayQuestionIds, $preferenceQuestionId, $categories, &$allApplicantsData) {
            // Pre-load scores for this chunk
            $allScores = AppScore::whereIn('applicant_id', $applicants->pluck('id'))
                ->where(function ($query) use ($essayQuestionIds) {
                    $query->whereIn('question_id', $essayQuestionIds)
                          ->orWhereIn('question_uuid', $essayQuestionIds);
                })
                ->get()
                ->groupBy('applicant_id');

            // Load answers for this chunk
            $applicants->load(['appAnswers' => function ($query) use ($essayQuestionIds, $preferenceQuestionId) {
                $query->whereIn('question_id', array_merge($essayQuestionIds, $preferenceQuestionId ? [$preferenceQuestionId] : []))
                      ->whereNotNull('answer')
                      ->where('answer', '!=', '');
            }]);

            $chunkData = $this->processApplicantsData($applicants, $essayQuestions, $allScores, $preferenceQuestionId, $categories);
            $allApplicantsData = array_merge($allApplicantsData, $chunkData);
        });

        // Apply cutoff filtering
        $allApplicantsData = $this->applyCutoffFilter($allApplicantsData, $application);

        // Sort by total average score (descending)
        usort($allApplicantsData, function ($a, $b) {
            return $b['total_average_score'] <=> $a['total_average_score'];
        });

        // Cache the results for 5 minutes
        cache()->put($cacheKey, $allApplicantsData, 300);

        return $allApplicantsData;
    }

    private function processApplicantsData($applicants, $essayQuestions, $allScores, $preferenceQuestionId, $categories): array
    {
        $applicantsData = [];

        foreach ($applicants as $applicant) {
            $applicantData = [
                'id' => $applicant->id,
                'uuid' => $applicant->uuid,
                'name' => $applicant->name ?? $applicant->reddit_user ?? 'User #' . $applicant->id,
                'individual_scores' => [],
                'total_average_score' => 0,
                'main_preferences' => [],
                'non_main_preferences' => [],
            ];

            // Get scores for this applicant
            $applicantScores = $allScores->get($applicant->id, collect());

            // Calculate individual question scores
            $questionAverages = [];
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
                $preferenceAnswer = $applicant->appAnswers->firstWhere('question_id', $preferenceQuestionId);

                if ($preferenceAnswer) {
                    $preferenceData = json_decode($preferenceAnswer->answer, true);
                    
                    if ($preferenceData) {
                        // Parse main preferences (ordered list)
                        if (isset($preferenceData['main_order']) && $preferenceData['main_order']) {
                            $mainOrderData = json_decode($preferenceData['main_order'], true);
                            if (is_array($mainOrderData)) {
                                $applicantData['main_preferences'] = $this->formatPreferences($mainOrderData, $categories);
                            }
                        }

                        // Parse non-main preferences
                        if (isset($preferenceData['non_main']) && is_array($preferenceData['non_main'])) {
                            $applicantData['non_main_preferences'] = $this->formatPreferences($preferenceData['non_main'], $categories);
                        }
                    }
                }
            }

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
                        \Log::warning('Category not found for category_id: ' . $categoryId);
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
                        \Log::warning('Category not found for nested ID: ' . $categoryId);
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
                    \Log::warning('Category not found for ID: ' . $categoryId);
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
                    \Log::warning('Category not found for numeric ID: ' . $preference);
                }
            } else {
                \Log::warning('Unexpected preference format:', ['preference' => $preference]);
            }
        }

        return $formatted;
    }

    private function applyCutoffFilter(array $applicantsData, Application $application): array
    {
        return array_filter($applicantsData, function ($applicant) use ($application) {
            // Check if total average score is above base cutoff
            if ($applicant['total_average_score'] >= $this->baseCutoff) {
                return true;
            }

            // Check if answered "Yes" to both multiple choice questions
            if ($this->answeredYesToBothMultipleChoice($applicant['id'], $application)) {
                return true;
            }

            return false;
        });
    }

    private function answeredYesToBothMultipleChoice(int $applicantId, Application $application): bool
    {
        if (!$application->form) {
            return false;
        }

        // Find multiple choice questions
        $multipleChoiceQuestions = collect($application->form)->filter(function ($question) {
            return $question['type'] === 'multiple_choice';
        });

        if ($multipleChoiceQuestions->count() < 2) {
            return false;
        }

        $yesAnswers = 0;
        foreach ($multipleChoiceQuestions as $question) {
            $answer = AppAnswer::where('applicant_id', $applicantId)
                ->where('question_id', $question['id'])
                ->first();

            if ($answer) {
                // Find the "Yes" option UUID in the question
                $yesOptionUuid = null;
                if (isset($question['options']) && is_array($question['options'])) {
                    foreach ($question['options'] as $option) {
                        \Log::info('Checking option:', [
                            'option_text' => $option['option'] ?? 'N/A',
                            'option_uuid' => $option['id'] ?? 'N/A',
                        ]);
                        
                        if (isset($option['option']) && strtolower(trim($option['option'])) === 'yes') {
                            $yesOptionUuid = $option['id'] ?? null;
                            \Log::info('Found Yes option:', [
                                'yes_option_uuid' => $yesOptionUuid,
                                'answer_matches' => $answer->answer === $yesOptionUuid
                            ]);
                            break;
                        }
                    }
                }

                // Check if the answer matches the "Yes" option UUID
                if ($yesOptionUuid && $answer->answer === $yesOptionUuid) {
                    $yesAnswers++;
                    \Log::info('Yes answer counted!', [
                        'applicant_id' => $applicantId,
                        'question_id' => $question['id'],
                        'total_yes_answers' => $yesAnswers
                    ]);
                }
            }
        }

        return $yesAnswers >= 2;
    }

    protected function getHeaderActions(): array
    {
        return [
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
            $cacheKey = 'juror_allocations_' . $application->year . '_' . $application->updated_at->timestamp;
            Cache::forget($cacheKey);
        }
    }


    private function debugPreferences(): void
    {
        $application = $this->getApplication();
        if (!$application || !$application->form) {
            \Log::info('No application found for debugging');
            return;
        }

        // Get preference question
        $preferenceQuestion = collect($application->form)->first(function ($question) {
            return $question['type'] === 'preference';
        });

        if (!$preferenceQuestion) {
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
                'preference_answer' => $sampleApplicant->appAnswers->first()->answer ?? 'No answer'
            ]);
        } else {
            \Log::info('No applicants with preferences found');
        }

        // Get categories
        $categories = Category::where('year', $application->year)->get();
        \Log::info('Available categories:', $categories->pluck('name', 'id')->toArray());
    }
}
