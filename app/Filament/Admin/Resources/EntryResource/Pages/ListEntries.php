<?php

namespace App\Filament\Admin\Resources\EntryResource\Pages;

use App\Filament\Admin\Resources\EntryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;

class ListEntries extends ListRecords
{
    protected static string $resource = EntryResource::class;

    protected function getHeaderActions(): array
    {
        $selectedType = session('selected-type-filter');
        $selectedTypeName = $selectedType ? ucfirst($selectedType) : 'All Types';

        $typeOptions = [
            'anime' => 'Anime',
            'char' => 'Characters', 
            'va' => 'Voice Actors',
            'theme' => 'Themes',
        ];

        $dropdownActions = [];
        
        // Add "All Types" option
        $dropdownActions[] = Actions\Action::make('all_types')
            ->label('All Types')
            ->action(function () {
                session(['selected-type-filter' => null]);
                $this->dispatch('type-filter-updated');
            })
            ->icon('heroicon-o-x-mark');
        
        // Add individual type options
        foreach ($typeOptions as $type => $label) {
            $dropdownActions[] = Actions\Action::make('type_' . $type)
                ->label($label)
                ->action(function () use ($type) {
                    session(['selected-type-filter' => $type]);
                    $this->dispatch('type-filter-updated');
                })
                ->icon('heroicon-o-funnel');
        }

        return [
            Actions\ActionGroup::make($dropdownActions)
                ->label($selectedTypeName)
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

    #[On('type-filter-updated')]
    public function refreshOnTypeFilter()
    {
        $this->resetTable();
    }
}
