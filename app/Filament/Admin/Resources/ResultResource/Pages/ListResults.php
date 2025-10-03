<?php

namespace App\Filament\Admin\Resources\ResultResource\Pages;

use App\Filament\Admin\Resources\ResultResource;
use App\Models\Category;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Livewire\Attributes\On;

class ListResults extends ListRecords
{
    protected static string $resource = ResultResource::class;

    public $selectedCategory = null;

    protected function getHeaderActions(): array
    {
        $selectedCategoryId = session('selected-category-filter');
        $selectedCategoryName = $selectedCategoryId ? 
            Category::find($selectedCategoryId)?->name ?? 'Unknown Category' : 
            'All Categories';

        $filterYear = session('selected-year-filter') ?? intval(date('Y'));
        $categories = Category::where('year', $filterYear)->orderBy('order')->get();
        
        $dropdownActions = [];
        
        // Add "All Categories" option
        $dropdownActions[] = Actions\Action::make('all_categories')
            ->label('All Categories')
            ->action(function () {
                session(['selected-category-filter' => null]);
                $this->dispatch('category-filter-updated');
            })
            ->icon('heroicon-o-x-mark');
        
        // Add individual category options
        foreach ($categories as $category) {
            $dropdownActions[] = Actions\Action::make('category_' . $category->id)
                ->label($category->name)
                ->action(function () use ($category) {
                    session(['selected-category-filter' => $category->id]);
                    $this->dispatch('category-filter-updated');
                })
                ->icon('heroicon-o-funnel');
        }

        return [
            Actions\ActionGroup::make($dropdownActions)
                ->label("{$selectedCategoryName}")
                ->icon('heroicon-o-funnel')
                ->color('gray')
                ->outlined()
                ->button(),
            Actions\CreateAction::make(),
        ];
    }

    #[On('filter-year-updated')]
    public function refreshOnYearFilter()
    {
        $this->resetTable();
    }

    #[On('category-filter-updated')]
    public function refreshOnCategoryFilter()
    {
        $this->resetTable();
    }

}
