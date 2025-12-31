<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ApplicationResource\Pages;
use App\Filament\Admin\Resources\ApplicationResource\Pages\ApplicationDashboard;
use App\Filament\Admin\Resources\ApplicationResource\Pages\ApplicationGrading;
use App\Filament\Admin\Resources\ApplicationResource\RelationManagers;
use App\Models\Application;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Str;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    public static function canAccess(): bool
    {
        return auth()->user()?->role >= 2;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('year')
                    ->required()
                    ->numeric()
                    ->default(app('current-year')),
                DateTimePicker::make('start_time')
                    ->required()
                    ->label('Start Time')
                    ->displayFormat('M j, Y g:i A')
                    ->seconds(false),
                DateTimePicker::make('end_time')
                    ->required()
                    ->label('End Time')
                    ->displayFormat('M j, Y g:i A')
                    ->seconds(false),
                Repeater::make('form')
                    ->label('Application Questions')
                    ->schema([
                        Hidden::make('id')
                            ->default(fn () => Str::uuid()->toString()),
                        TextInput::make('question')
                            ->label('Question Text')
                            ->required()
                            ->columnSpanFull(),
                        Select::make('type')
                            ->label('Question Type')
                            ->options([
                                'multiple_choice' => 'Multiple Choice',
                                'essay' => 'Essay',
                                'preference' => 'Preference',
                            ])
                            ->required()
                            ->live(),
                        Repeater::make('options')
                            ->label('Answer Options')
                            ->schema([
                                Hidden::make('id')
                                    ->default(fn () => Str::uuid()->toString()),
                                TextInput::make('option')
                                    ->label('Option Text')
                                    ->required(),
                            ])
                            ->visible(fn (Get $get): bool => $get('type') === 'multiple_choice')
                            ->addActionLabel('Add Option')
                            ->defaultItems(0),
                        TextInput::make('character_limit')
                            ->label('Character Limit')
                            ->numeric()
                            ->default(5000)
                            ->minValue(100)
                            ->maxValue(50000)
                            ->suffix('characters')
                            ->helperText('Maximum characters allowed for this essay question')
                            ->visible(fn (Get $get): bool => $get('type') === 'essay'),
                        MarkdownEditor::make('instructions')
                            ->label('Additional Instructions')
                            ->columnSpanFull()
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('storage/instructions'),
                        Repeater::make('sample_answers')
                            ->label('Sample Answers')
                            ->schema([
                                Hidden::make('id')
                                    ->default(fn () => Str::uuid()->toString()),
                                MarkdownEditor::make('answer')
                                    ->label('Sample Answer')
                                    ->required()
                                    ->columnSpanFull()
                                    ->fileAttachmentsDisk('public')
                                    ->fileAttachmentsDirectory('storage/sample-answers'),
                                TextInput::make('score')
                                    ->label('Score')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->maxValue(4)
                                    ->suffix('/4')
                                    ->helperText('Score out of 4 for this sample answer'),
                            ])
                            ->visible(fn (Get $get): bool => $get('type') === 'essay')
                            ->addActionLabel('Add Sample Answer')
                            ->defaultItems(0)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->addActionLabel('Add Question')
                    ->defaultItems(0)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('year'),
                TextColumn::make('start_time'),
                TextColumn::make('end_time'),
                TextColumn::make('form'),
            ])
            ->filters([
            ])
            ->actions([
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ApplicationDashboard::route('/'),
            'create' => Pages\CreateApplication::route('/create'),
            'edit' => Pages\EditApplication::route('/{record}/edit'),
            'grading' => Pages\ApplicationGrading::route('/grading'),
        ];
    }
}
