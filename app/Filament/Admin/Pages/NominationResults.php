<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use App\Models\Category;
use Livewire\Attributes\Computed;

class NominationResults extends Page
{
    protected string $view = 'filament.admin.pages.nomination-results';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->role >= 2;
    }

    #[Computed]
    public function categories()
    {
        return Category::where('year', app('current-year'))
            ->has('eligibles')
            ->orderBy('order')
            ->get();
    }
}
