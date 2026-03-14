<?php

namespace App\Filament\Admin\Resources\HonorableMentions;

use App\Filament\Admin\Resources\HonorableMentions\Pages\CreateHonorableMention;
use App\Filament\Admin\Resources\HonorableMentions\Pages\EditHonorableMention;
use App\Filament\Admin\Resources\HonorableMentions\Pages\ListHonorableMentions;
use App\Filament\Admin\Resources\HonorableMentions\Schemas\HonorableMentionForm;
use App\Filament\Admin\Resources\HonorableMentions\Tables\HonorableMentionsTable;
use App\Models\HonorableMention;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HonorableMentionResource extends Resource
{
    protected static ?string $model = HonorableMention::class;

    // protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    // TODO: move sorts to a single "panel order" file ideally
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return HonorableMentionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HonorableMentionsTable::configure($table);
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
            'index' => ListHonorableMentions::route('/'),
            'create' => CreateHonorableMention::route('/create'),
            'edit' => EditHonorableMention::route('/{record}/edit'),
        ];
    }
}
