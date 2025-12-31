<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ResultResource\Pages;
use App\Filament\Admin\Resources\ResultResource\RelationManagers;
use App\Models\Result;
use App\Models\Category;
use App\Models\Entry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ResultResource extends Resource
{
    protected static ?string $model = Result::class;

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
                    ->default(date('Y')),
                Select::make('category_id')
                    ->label('Category')
                    ->required()
                    ->options(function () {
                        $filterYear = session('selected-year-filter') ?? intval(date('Y'));
                        return Category::where('year', $filterYear)->pluck('name', 'id');
                    })
                    ->searchable(),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                FileUpload::make('image')
                    ->image()
                    ->directory('entry')
                    ->disk('public')
                    ->required(),
                Select::make('entry_id')
                    ->label('Entry')
                    ->required()
                    ->options(function () {
                        $filterYear = session('selected-year-filter') ?? intval(date('Y'));
                        return Entry::where('year', $filterYear)->pluck('name', 'id');
                    })
                    ->searchable(),
                TextInput::make('jury_rank')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                TextInput::make('public_rank')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                MarkdownEditor::make('description')
                    ->required()
                    ->columnSpanFull()
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('descriptions'),
                Repeater::make('staff_credits')
                    ->label('Staff Credits')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('role')
                            ->label('Role/Position')
                            ->required()
                            ->placeholder('e.g., Director, Studio, Key Animation'),
                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->placeholder('Person\'s name'),
                    ])
                    ->columns(2)
                    ->addActionLabel('Add Staff Member')
                    ->helperText('')
                    ->defaultItems(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->modifyQueryUsing(function (Builder $query) {
                $filterYear = session('selected-year-filter') ?? intval(date('Y'));
                $query = $query->where('year', $filterYear);
                
                $selectedCategory = session('selected-category-filter');
                if ($selectedCategory) {
                    $query = $query->where('category_id', $selectedCategory);
                }
                
                return $query;
            })
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->width('500px'),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('entry.name')
                    ->label('Entry')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('jury_rank')
                    ->label('Jury Rank')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('public_rank')
                    ->label('Public Rank')
                    ->numeric()
                    ->sortable(),
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
            'index' => Pages\ListResults::route('/'),
            'create' => Pages\CreateResult::route('/create'),
            'edit' => Pages\EditResult::route('/{record}/edit'),
        ];
    }
}
