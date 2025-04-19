<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CategoryResource\Pages;
use App\Filament\Admin\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                TextInput::make('year')
                    ->default(date('Y'))
                    ->required()
                    ->numeric()
                    ->maxLength(4)
                    ->reactive(),
                TextInput::make('name')
                    ->required()
                    // Name suggestions based on previous years' categories, excluding already present current year names
                    ->datalist(function (callable $get) {
                        $currYear = $get('year');
                        $namesQuery = Category::query();
                        $filterQuery = $namesQuery->clone()->where('year', '=', $currYear);
                        $names = $namesQuery->whereNotIn('name', $filterQuery->get()->pluck('name'))
                            ->get()
                            ->pluck('name');
                        return $names;
                    })
                    ->maxLength(255),
                Select::make('type')
                    ->required()
                    ->options([
                        'genre' => 'Genre',
                        'production' => 'Production',
                        'main' => 'Main'
                    ])
                    ->default('genre')
                /*
                TextInput::make('order')
                    ->required()
                    ->maxLength(255)
                */
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->columns([
                //
                TextColumn::make('order'),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('type')
                    ->searchable(),
                TextColumn::make('year'),
            ])
            ->defaultSort('order')
            ->filters([
                //
                SelectFilter::make('type')
                    ->options([
                        'genre' => 'Genre',
                        'production' => 'Production',
                        'main' => 'Main'
                    ])

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
