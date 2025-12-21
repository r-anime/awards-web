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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Application::unguard();
        // Unguarding category model as per filament doc
        Category::unguard();

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
}
