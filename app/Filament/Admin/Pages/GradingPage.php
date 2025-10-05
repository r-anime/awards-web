<?php

namespace App\Filament\Admin\Pages;

use App\Models\Application;
use App\Models\AppAnswer;
use App\Models\AppScore;
use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class GradingPage extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-academic-cap';
    
    protected string $view = 'filament.admin.pages.grading-page';
    
    protected static ?string $title = 'Grade Applications';
    
    protected static ?string $navigationLabel = 'Grade Applications';
    
    protected static ?int $navigationSort = 3;
    
    protected static ?string $slug = 'application/grading/{userUuid?}';
    
    protected static bool $shouldRegisterNavigation = false;
    
    public ?User $ungradedApplication = null;
    public array $grades = [];
    public array $comments = [];
    public ?string $userUuid = null;
    
    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->role >= 2;
    }
    
    public static function getUrlForUser(string $userUuid, array $parameters = [], bool $isAbsolute = true): string
    {
        return static::getUrl(array_merge($parameters, ['userUuid' => $userUuid]), $isAbsolute);
    }
    
    public function mount(?string $userUuid = null): void
    {
        $this->userUuid = $userUuid;
        $this->loadUngradedApplication();
    }
    
    public function loadUngradedApplication(): void
    {
        // If a specific user UUID is provided, grade that user
        if ($this->userUuid) {
            $this->ungradedApplication = User::where('uuid', $this->userUuid)->first();
            if ($this->ungradedApplication) {
                $this->loadExistingGrades();
            }
            return;
        }
        
        // Get the latest application
        $application = Application::orderBy('year', 'desc')->first();
        
        if (!$application) {
            return;
        }
        
        // Find applications that the current user hasn't graded yet
        $applicants = User::whereHas('appAnswers')->get();
        $ungradedApplicants = [];
        
        foreach ($applicants as $applicant) {
            $hasBeenGraded = AppScore::where('applicant_id', $applicant->id)
                ->where('scorer_id', auth()->id())
                ->exists();
            
            if (!$hasBeenGraded) {
                $ungradedApplicants[] = $applicant;
            }
        }
        
        // Randomly select an ungraded applicant
        if (!empty($ungradedApplicants)) {
            $randomApplicant = $ungradedApplicants[array_rand($ungradedApplicants)];
            $this->redirect(static::getUrlForUser($randomApplicant->uuid));
            return;
        }
    }
    
    public function loadExistingGrades(): void
    {
        if (!$this->ungradedApplication) {
            return;
        }
        
        $existingScores = AppScore::where('applicant_id', $this->ungradedApplication->id)
            ->where('scorer_id', auth()->id())
            ->pluck('score', 'question_id')
            ->toArray();
            
        $existingComments = AppScore::where('applicant_id', $this->ungradedApplication->id)
            ->where('scorer_id', auth()->id())
            ->pluck('comment', 'question_id')
            ->toArray();
            
        $this->grades = $existingScores;
        $this->comments = $existingComments;
    }
    
    public function getEssayQuestions(): array
    {
        $application = Application::orderBy('year', 'desc')->first();
        
        if (!$application || !$application->form) {
            return [];
        }
        
        $essayQuestions = [];
        foreach ($application->form as $question) {
            if ($question['type'] === 'essay') {
                $essayQuestions[] = $question;
            }
        }
        
        return $essayQuestions;
    }
    
    public function getExistingAnswers(): array
    {
        if (!$this->ungradedApplication) {
            return [];
        }
        
        return AppAnswer::where('applicant_id', $this->ungradedApplication->id)
            ->pluck('answer', 'question_id')
            ->toArray();
    }
    
    public function saveGrades(): void
    {
        if (!$this->ungradedApplication) {
            Notification::make()
                ->title('Error')
                ->body('No application selected for grading.')
                ->danger()
                ->send();
            return;
        }
        
        foreach ($this->grades as $questionId => $score) {
            if ($score !== null && $score !== '') {
                AppScore::updateOrCreate(
                    [
                        'applicant_id' => $this->ungradedApplication->id,
                        'scorer_id' => auth()->id(),
                        'question_id' => $questionId,
                    ],
                    [
                        'score' => $score,
                        'comment' => $this->comments[$questionId] ?? '',
                    ]
                );
            }
        }
        
        Notification::make()
            ->title('Success')
            ->body('Grades saved successfully!')
            ->success()
            ->send();
            
        // Redirect to the general grading page to find the next ungraded user
        $this->redirect(static::getUrl());
    }
    
    public function refreshApplications(): void
    {
        $this->redirect(static::getUrl());
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action('refreshApplications'),
            Action::make('save')
                ->label('Save Grades')
                ->color('success')
                ->action('saveGrades')
                ->visible(fn() => $this->ungradedApplication !== null),
        ];
    }
}
