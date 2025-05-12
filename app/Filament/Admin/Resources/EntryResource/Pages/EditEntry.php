<?php

namespace App\Filament\Admin\Resources\EntryResource\Pages;

use App\Filament\Admin\Resources\EntryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEntry extends EditRecord
{
    protected static string $resource = EntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
