<?php

namespace App\Providers;

use App\Models\Application;
use App\Models\Category;
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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Application::unguard();
        // Unguarding category model as per filament doc
        Category::unguard();

        // Year filter dropdown

        FilamentView::registerRenderHook(
            PanelsRenderHook::USER_MENU_BEFORE,
            fn(): string => Blade::render('@livewire(\'year-filter\')'),
        );
    }
}
