<?php

namespace App\Filament\Admin\Resources\CategoryResource\Pages;

use App\Filament\Admin\Resources\CategoryResource;
use App\Models\CategoryInfo;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // TODO: implement auto-determined order
        $data['order'] = 5;

        // Don't save 'info' to categories table; it's persisted to category_infos in afterCreate()
        unset($data['info']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $data = $this->form->getState();
        $info = $data['info'] ?? null;
        if ($info !== null) {
            CategoryInfo::create([
                'category_id' => $this->record->id,
                'description' => $info['description'] ?? '',
                'sotc_blurb' => $info['sotc_blurb'] ?? null,
            ]);
        }
    }
}
