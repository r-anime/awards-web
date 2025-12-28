<?php

namespace App\Filament\Admin\Resources\CategoryResource\RelationManagers;

use App\Models\Entry;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class EligiblesRelationManager extends RelationManager
{
    protected static string $relationship = 'eligibles';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('entry_id')
                    ->label('Entry')
                    ->required()
                    ->options(function (Get $get) {
                        $parent_cat = $this->getOwnerRecord();
                        $year = $parent_cat->year;

                        // TODO: Implement type constraints for search

                        return Entry::where('year', $year)
                            ->pluck('name', 'id');
                    })
                    ->searchable(),
                Toggle::make('active')
                    ->required()
                    ->default(true),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        Fieldset::make()
                            ->columns(1)
                            ->contained(false)
                            ->schema([
                                TextEntry::make('entry.name'),
                                TextEntry::make('entry.year'),
                                TextEntry::make('entry_id')
                                    ->numeric(),
                                IconEntry::make('active')
                                    ->boolean(),
                                TextEntry::make('created_at')
                                    ->dateTime()
                                    ->placeholder('-'),
                                TextEntry::make('updated_at')
                                    ->dateTime()
                                    ->placeholder('-'),
                            ]),
                        ImageEntry::make('entry.image')
                            ->imageSize(400)
                            ->disk('public'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('eligible')
            ->columns([
                TextColumn::make('entry_id')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('entry.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('entry.type')
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('active'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                // AssociateAction::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                // EditAction::make(),
                // DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
