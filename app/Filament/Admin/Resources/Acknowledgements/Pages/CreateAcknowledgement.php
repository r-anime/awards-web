<?php

namespace App\Filament\Admin\Resources\Acknowledgements\Pages;

use App\Filament\Admin\Resources\Acknowledgements\AcknowledgementResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAcknowledgement extends CreateRecord
{
    protected static string $resource = AcknowledgementResource::class;
}
