<?php

namespace App\Filament\Admin\Resources\EntryResource\Pages;

use App\Filament\Admin\Resources\EntryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEntry extends CreateRecord
{
    protected static string $resource = EntryResource::class;
}
