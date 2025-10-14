<?php

namespace App\Filament\Admin\Resources\ResultResource\Pages;

use App\Filament\Admin\Resources\ResultResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditResult extends EditRecord
{
    protected static string $resource = ResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (!isset($data['staff_credits']) || !is_array($data['staff_credits'])) {
            $data['staff_credits'] = [];
        }

        if (isset($data['description']) && !is_string($data['description'])) {
            $data['description'] = (string) $data['description'];
        }

        return $data;
    }
}
