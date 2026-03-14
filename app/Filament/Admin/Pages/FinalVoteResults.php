<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use App\Models\Category;
use Livewire\Attributes\Computed;

class FinalVoteResults extends Page
{
    protected string $view = 'filament.admin.pages.final-vote-results';

    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->role >= 2;
    }

    #[Computed]
    public function categories()
    {
        return Category::where('year', app('current-year'))
            ->has('nominees')
            ->orderBy('order')
            ->get();
    }
}
