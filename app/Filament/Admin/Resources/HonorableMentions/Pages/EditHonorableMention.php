<?php

namespace App\Filament\Admin\Resources\HonorableMentions\Pages;

use App\Filament\Admin\Resources\HonorableMentions\HonorableMentionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHonorableMention extends EditRecord
{
    protected static string $resource = HonorableMentionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
