<?php

namespace App\Filament\Admin\Resources\ResultResource\Pages;

use App\Filament\Admin\Resources\ResultResource;
use App\Models\Category;
use App\Models\Entry;
use App\Models\FinalVote;
use App\Models\Option;
use App\Models\Result;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Livewire\Attributes\On;

class ListResults extends ListRecords
{
    protected static string $resource = ResultResource::class;

    public $selectedCategory = null;

    public function addResultsFromVotingEnded(): bool
    {
        $endDate = Option::get('final_voting_end_date', '');
        if ($endDate === '' || $endDate === null) {
            return false;
        }
        return now()->gte(Carbon::parse($endDate));
    }

    protected function getHeaderActions(): array
    {
        $selectedCategoryId = session('selected-category-filter');
        $selectedCategoryName = $selectedCategoryId ? 
            Category::find($selectedCategoryId)?->name ?? 'Unknown Category' : 
            'All Categories';

        $filterYear = session('selected-year-filter') ?? intval(app('current-year'));
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

        $votingEnded = $this->addResultsFromVotingEnded();
        $disabledTooltip = 'Available after the final voting period ends. Set the end date in Options if needed.';
        $addFromVotingAction = Actions\Action::make('add_results_from_voting')
            ->label('Add Results from Voting')
            ->action(function () use ($filterYear) {
                $this->importResultsFromVoting((int) $filterYear);
            })
            ->icon('heroicon-o-arrow-down-tray')
            ->color('gray')
            ->outlined()
            ->disabled(!$votingEnded)
            ->tooltip($votingEnded ? null : $disabledTooltip)
            ->extraAttributes($votingEnded ? [] : ['title' => $disabledTooltip]);

        return [
            Actions\ActionGroup::make($dropdownActions)
                ->label("{$selectedCategoryName}")
                ->icon('heroicon-o-funnel')
                ->color('gray')
                ->outlined()
                ->button(),
            $addFromVotingAction,
            Actions\CreateAction::make(),
        ];
    }

    protected function importResultsFromVoting(int $year): void
    {
        $categories = Category::where('year', $year)->orderBy('order')->get();
        $created = 0;

        foreach ($categories as $category) {
            $voteCounts = FinalVote::query()
                ->selectRaw('final_votes.entry_id, COUNT(*) as vote_count')
                ->where('final_votes.category_id', $category->id)
                ->groupBy('final_votes.entry_id')
                ->orderByDesc('vote_count')
                ->get();

            foreach ($voteCounts as $row) {
                $exists = Result::where('year', $year)
                    ->where('category_id', $category->id)
                    ->where('entry_id', $row->entry_id)
                    ->exists();
                if ($exists) {
                    continue;
                }

                $entry = Entry::find($row->entry_id);
                if (!$entry) {
                    continue;
                }

                Result::create([
                    'year' => $year,
                    'category_id' => $category->id,
                    'name' => $entry->name,
                    'image' => $entry->image ?? '',
                    'entry_id' => $entry->id,
                    'jury_rank' => -1,
                    'public_rank' => (int) $row->vote_count,
                    'description' => '',
                    'staff_credits' => null,
                ]);
                $created++;
            }
        }

        Notification::make()
            ->title($created === 0 ? 'No new results to add' : "Added {$created} result(s) from voting.")
            ->success()
            ->send();

        $this->resetTable();
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
