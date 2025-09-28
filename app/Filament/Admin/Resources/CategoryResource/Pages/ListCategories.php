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
        $this->resetTable();
    }

    public function reorderTable(array $order, string|int|null $draggedRecordKey = null): void
    {
        $filterYear = session('selected-year-filter') ?? intval(date('Y'));
        $categories = Category::where('year', $filterYear)->get();
        
        foreach ($order as $index => $categoryId) {
            $category = $categories->find($categoryId);
            if ($category) {
                $category->update(['order' => $index + 1]);
            }
        }
        
        $this->resetTable();
    }
}
