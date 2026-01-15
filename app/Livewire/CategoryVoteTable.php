<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\NomineeVote;
use App\Models\Entry;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class CategoryVoteTable extends Component implements HasTable, HasActions, HasSchemas
{
    use InteractsWithTable;
    use InteractsWithActions;
    use InteractsWithSchemas;

    public int $categoryId;
    public string $categoryName;

    public function mount(int $categoryId, string $categoryName)
    {
        $this->categoryId = $categoryId;
        $this->categoryName = $categoryName;
    }

    public function table(Table $table): Table
    {
        $currentYear = app('current-year');
        
        $query = NomineeVote::selectRaw('nominee_votes.entry_id, entries.name as entry_name, parents.name as parent_name, COUNT(*) as vote_count')
            ->join('entries', 'nominee_votes.entry_id', '=', 'entries.id')
            ->leftJoin('entries as parents', 'entries.parent_id', '=', 'parents.id')
            ->where('nominee_votes.category_id', $this->categoryId)
            ->groupBy('nominee_votes.entry_id')
            ->limit(10);
        
        return $table
            ->query($query)
            ->paginated(false)
            ->defaultSort('vote_count', 'desc')
            // default sort Key by id breaks the table.
            ->defaultKeySort(false)
            ->searchable(false)
            ->columns([
                TextColumn::make('entry_name')
                    ->label('Entry'),
                TextColumn::make('parent_name')
                    ->label('Parent'),
                TextColumn::make('vote_count')
                    ->label('Votes')
                    ->sortable()
                    ->numeric()
                    ->alignEnd(),
            ]);
    }

    public function getTableRecordKey($record): string
    {
        return (string) ($record->entry_id ?? $this->categoryId);
    }

    public function render()
    {
        return view('livewire.category-vote-table');
    }
}
