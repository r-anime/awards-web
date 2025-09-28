<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\EntryResource\Pages;
use App\Filament\Admin\Resources\EntryResource\RelationManagers;
use App\Models\Entry;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;

use Filament\Tables\Columns\TextColumn;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;


class EntryResource extends Resource
{
    protected static ?string $model = Entry::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(),
                Select::make('type')
                    ->options([
                        'anime' => 'Anime',
                        'char'  => 'Character',
                        'va'    => 'Voice Actor',
                        'theme' => 'Theme (OP/ED)',
                    ])->required()->live(),
                Select::make('parent_id')
                    ->visible( fn (Get $get): bool  => (filled($get('type')) && $get('type') != 'anime'))
                    ->required(fn (Get $get): bool  => (filled($get('type')) && $get('type') != 'anime'))
                    ->label('Parent')
                    ->options(function (Get $get) { 
                        $selectedType = $get('type');
                        $filterType = 'anime';
                        if ($selectedType == 'char' || $selectedType == 'theme'){
                            $filterType = 'anime';
                        } else if ($selectedType == 'va'){
                            $filterType = 'char';
                        }
                        return Entry::where('type', $filterType)->pluck('name', 'id');
                    })
                    ->searchable(),           
                TextInput::make('theme_version')
                    ->visible(fn (Get $get): bool   => ($get('type') == 'theme'))
                    ->required(fn (Get $get): bool  => ($get('type') == 'theme')),
                TextInput::make('link')
                    ->visible(fn (Get $get): bool   => ($get('type') == 'theme'))
                    ->required(fn (Get $get): bool  => ($get('type') == 'theme')),
                TextInput::make('year')->numeric()->required(),
                FileUpload::make('image')
                    ->image()
                    ->directory('entry')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $filterYear = session('selected-year-filter') ?? intval(date('Y'));
                return $query->where('year', $filterYear);
            })
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('year'),
                TextColumn::make('parent.name')->visible(fn($livewire) => ($livewire->activeTab !== 'anime'))
                    ->searchable(),
                TextColumn::make('parent.parent.name')->visible(fn($livewire) => ($livewire->activeTab == 'va'))
                    ->searchable(),
            ])
            ->filters([
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListEntries::route('/'),
            'create' => Pages\CreateEntry::route('/create'),
            'edit' => Pages\EditEntry::route('/{record}/edit'),
        ];
    }
}
