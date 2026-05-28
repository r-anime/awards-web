<?php

namespace App\Filament\Admin\Resources\Acknowledgements;

use App\Filament\Admin\Resources\Acknowledgements\Pages\CreateAcknowledgement;
use App\Filament\Admin\Resources\Acknowledgements\Pages\EditAcknowledgement;
use App\Filament\Admin\Resources\Acknowledgements\Pages\ListAcknowledgements;
use App\Filament\Admin\Resources\Acknowledgements\Schemas\AcknowledgementForm;
use App\Filament\Admin\Resources\Acknowledgements\Tables\AcknowledgementsTable;
use App\Models\Acknowledgement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AcknowledgementResource extends Resource
{
    protected static ?string $model = Acknowledgement::class;

    // protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return AcknowledgementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AcknowledgementsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAcknowledgements::route('/'),
            'create' => CreateAcknowledgement::route('/create'),
            'edit' => EditAcknowledgement::route('/{record}/edit'),
        ];
    }
}
