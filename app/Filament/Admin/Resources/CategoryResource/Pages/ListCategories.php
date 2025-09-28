<?php

namespace App\Filament\Admin\Resources\CategoryResource\Pages;

use App\Filament\Admin\Resources\CategoryResource;
use App\Models\Category;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Livewire\Attributes\On;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    #[On('filter-year-updated')]
    public function refreshOnYearFilter()
    {
        // Force refresh the table by clearing the cache and reloading
        $this->resetTable();
    }

    public function reorderTable(array $order): void
    {
        $filterYear = session('selected-year-filter') ?? intval(date('Y'));
        
        // Get all categories for the current year
        $categories = Category::where('year', $filterYear)->get();
        
        // Update the order for each category based on the new order
        foreach ($order as $index => $categoryId) {
            $category = $categories->find($categoryId);
            if ($category) {
                $category->update(['order' => $index + 1]);
            }
        }
        
        // Refresh the table to show the new order
        $this->resetTable();
    }
}
