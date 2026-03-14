<?php

namespace App\Filament\Admin\Resources\CategoryResource\Pages;

use App\Filament\Admin\Resources\CategoryResource;
use App\Models\CategoryInfo;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['info'] = $this->record->info
            ? [
                'description' => $this->record->info->description ?? '',
                'sotc_blurb' => $this->record->info->sotc_blurb,
            ]
            : ['description' => '', 'sotc_blurb' => null];

        return $data;
    }

    protected function afterSave(): void
    {
        $data = $this->form->getState();
        $info = $data['info'] ?? null;
        if ($info !== null) {
            CategoryInfo::updateOrCreate(
                ['category_id' => $this->record->id],
                [
                    'description' => $info['description'] ?? '',
                    'sotc_blurb' => $info['sotc_blurb'] ?? null,
                ]
            );
        }
    }
}
