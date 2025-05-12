<?php

namespace App\Filament\Admin\Resources\EntryResource\Pages;

use App\Filament\Admin\Resources\EntryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListEntries extends ListRecords
{
    protected static string $resource = EntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'anime' => Tab::make('Anime')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'anime')),
            'char' => Tab::make('Characters')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'char')),
            'va' => Tab::make('Voice Actors')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'va')),
            'theme' => Tab::make('Themes')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'theme')),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'anime';
    }
}
