<?php

namespace App\Filament\Admin\Resources\Acknowledgements\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AcknowledgementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->reorderable('order')
            ->modifyQueryUsing(function (Builder $query) {
                $filterYear = session('selected-year-filter') ?? intval(app('current-year'));
                return $query->where('year', $filterYear)->orderBy('order');
            })->columns([
                TextColumn::make('year'),
                TextColumn::make('title'),
                TextColumn::make('order')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
