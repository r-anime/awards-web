<?php

namespace App\Filament\Admin\Resources\CategoryResource\RelationManagers;

use App\Models\HonorableMention;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HonorableMentionsRelationManager extends RelationManager
{
    protected static string $relationship = 'honorablementions';

    protected static ?string $title = 'Honorable Mentions';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('year')
                    ->default(fn () => $this->getOwnerRecord()->year)
                    ->required()
                    ->numeric()
                    ->hidden(),
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255),
                MarkdownEditor::make('writeup')
                    ->label('Writeup')
                    ->nullable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}

