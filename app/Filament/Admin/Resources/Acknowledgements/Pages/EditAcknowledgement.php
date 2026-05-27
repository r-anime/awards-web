<?php

namespace App\Filament\Admin\Resources\Acknowledgements\Pages;

use App\Filament\Admin\Resources\Acknowledgements\AcknowledgementResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAcknowledgement extends EditRecord
{
    protected static string $resource = AcknowledgementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if(!$data['subtitle'])
            $data['subtitle'] = ' ';
        return $data;
    }
}
