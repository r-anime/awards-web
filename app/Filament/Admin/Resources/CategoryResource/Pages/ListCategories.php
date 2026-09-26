<?php

namespace App\Filament\Admin\Resources\CategoryResource\Pages;

use App\Filament\Admin\Resources\CategoryResource;
use App\Models\Application;
use App\Models\Category;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    public function getCurrentYear(): int
    {
        return (int) (session('selected-year-filter') ?? app('current-year'));
    }

    public function isBeforeApplicationStart(): bool
    {
        $applicationStartDate = Application::where('year', $this->getCurrentYear())
            ->value('start_time');
        return $applicationStartDate === null 
            || now()->isBefore(Carbon::parse($applicationStartDate));
    }

    protected function getHeaderActions(): array
    {
        $copyCategoriesFromLastYearAction = Actions\Action::make('copy_categories_from_last_year')
            ->label('Copy Categories from Last Year')
            ->disabled(fn () => !$this->isBeforeApplicationStart())
            ->action(function (): void { $this->copyCategoriesFromLastYear(); });

        if(Category::query()->where('year', $this->getCurrentYear())->exists()) {
           $copyCategoriesFromLastYearAction->requiresConfirmation()
            ->modalHeading(fn (): string => sprintf(
                'Categories already exist for %d',
                $this->getCurrentYear()
            ))
            ->modalDescription(fn (): string => sprintf(
                'Categories already exist for %d. Categories with different names or types may still be added. Continue',
                $this->getCurrentYear()
            ));
        }
        return [
            $copyCategoriesFromLastYearAction,
            Actions\CreateAction::make(),
        ];
    }

    protected function copyCategoriesFromLastYear(): void
    {
        // Only copy if applications haven't opened
        if(!$this->isBeforeApplicationStart())
            return;

        $currentYear = $this->getCurrentYear();
        $previousYear = $currentYear - 1;

        DB::transaction(function () use ($currentYear, $previousYear): void {
            Category::where('year', $previousYear)
                ->orderBy('order')
                ->get()
                ->each(function (Category $category) use ($currentYear): void {
                    Category::firstOrCreate(
                        [
                            'year' => $currentYear,
                            'name' => $category->name,
                            'type' => $category->type,
                        ],
                        [
                            'entry_type' => $category->entry_type,
                            'order' => $category->order,
                        ],
                    );
                });
        });

        $this->resetTable();

    }

    #[On('filter-year-updated')]
    public function refreshOnYearFilter(): void
    {
        $this->resetTable();
    }

    public function reorderTable(array $order, string|int|null $draggedRecordKey = null): void
    {
        $filterYear = $this->getCurrentYear();
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
