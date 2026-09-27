<?php

namespace App\Filament\Admin\Resources\Acknowledgements\Pages;

use App\Filament\Admin\Resources\Acknowledgements\AcknowledgementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Livewire\Attributes\On;

class ListAcknowledgements extends ListRecords
{
    protected static string $resource = AcknowledgementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    #[On('filter-year-updated')]
    public function refreshOnYearFilter(): void
    {
        $this->resetTable();
    }
}
