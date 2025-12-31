<?php

namespace App\Providers;

use App\Models\Application;
use App\Models\Category;
use App\Services\ResultService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Binding singleton ResultService
        $this->app->singleton(ResultService::class, function() {return new ResultService();});
        
        // Global value of current awards year
        $this->app->singleton('current-year', function() { return now()->subMonths(3)->year; });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Application::unguard();
        // Unguarding category model as per filament doc
        Category::unguard();

        // Ensure storage directories exist
        $this->ensureStorageDirectoriesExist();

        // Share results years with all views for navbar dropdown
        View::composer('*', function ($view) {
            $resultService = app(ResultService::class);
            $years = $resultService->getYearList()
                ->pluck('year')
                ->sortDesc()
                ->values()
                ->all();
            $view->with('resultsYears', $years);
        });

        // Year filter dropdown
        FilamentView::registerRenderHook(
            PanelsRenderHook::USER_MENU_BEFORE,
            fn(): string => Blade::render('@livewire(\'year-filter\')'),
        );

        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('reddit', \SocialiteProviders\Reddit\Provider::class);
        });
    }

    /**
     * Ensure required storage directories exist with proper permissions
     */
    protected function ensureStorageDirectoriesExist(): void
    {
        $directories = [
            storage_path('app/public'),
            storage_path('app/public/entry'),
            storage_path('app/public/descriptions'),
        ];

        foreach ($directories as $directory) {
            if (!file_exists($directory)) {
                try {
                    mkdir($directory, 0775, true);
                    // Try to set permissions even if directory exists
                    @chmod($directory, 0775);
                } catch (\Exception $e) {
                    // Log but don't fail - permissions might be set manually
                    \Log::warning("Could not create directory: {$directory}. Error: " . $e->getMessage());
                }
            } else {
                // Ensure existing directory is writable
                if (!is_writable($directory)) {
                    @chmod($directory, 0775);
                }
            }
        }
    }
}
