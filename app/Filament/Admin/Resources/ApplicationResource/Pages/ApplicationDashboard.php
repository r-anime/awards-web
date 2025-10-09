<?php

namespace App\Filament\Admin\Resources\ApplicationResource\Pages;

use App\Filament\Admin\Resources\ApplicationResource;
use App\Filament\Admin\Resources\ApplicationResource\Pages\ApplicationGrading;
use App\Models\Application;
use Filament\Actions;
use Filament\Resources\Pages\Page;
use Livewire\Attributes\On;

class ApplicationDashboard extends Page
{
    protected static string $resource = ApplicationResource::class;
    
    protected string $view = 'filament.resources.application-resource.pages.application-dashboard';
    
    protected static ?string $title = 'Application Form';
    
    protected static ?string $navigationLabel = 'Application Form';

    public function getApplication(): ?Application
    {
        $filterYear = session('selected-year-filter') ?? intval(date('Y'));
        return Application::where('year', $filterYear)->first();
    }

    #[On('filter-year-updated')]
    public function refreshOnYearFilter()
    {
        // This will trigger a re-render of the page
    }

    protected function getHeaderActions(): array
    {
        $application = $this->getApplication();
        
        if (!$application) {
            return [
                Actions\CreateAction::make()
                    ->label('Create Application Form')
                    ->icon('heroicon-o-plus'),
            ];
        }
        
        $actions = [
            Actions\EditAction::make()
                ->record($application)
                ->label('Edit Application Form')
                ->icon('heroicon-o-pencil'),
        ];
        
        // Only show grade button for users with role 2+
        if (auth()->user()->role >= 2) {
            $actions[] = Actions\Action::make('grade')
                ->label('Grade Applications')
                ->icon('heroicon-o-academic-cap')
                ->color('success')
                ->url(\App\Filament\Admin\Pages\GradingPage::getUrl());
            
            $actions[] = Actions\Action::make('grading-overview')
                ->label('Grading Overview')
                ->icon('heroicon-o-table-cells')
                ->color('info')
                ->url(ApplicationGrading::getUrl());
        }
        
        return $actions;
    }
}
