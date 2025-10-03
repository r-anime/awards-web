<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Collection;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reddit_user')
                    ->label('Reddit Username')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('role')
                    ->label('Role Level')
                    ->badge()
                    ->color(fn (int $state): string => match ($state) {
                        0 => 'gray',
                        1 => 'warning',
                        2 => 'info',
                        3 => 'success',
                        4 => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        0 => 'User',
                        1 => 'Juror',
                        2 => 'Host',
                        3 => 'Moderator',
                        4 => 'Site Admin',
                        default => 'Unknown',
                    }),
                
                TextColumn::make('flags')
                    ->label('Flags')
                    ->badge()
                    ->color('warning'),
                
                TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                // Add filters here if needed
            ])
            ->actions([
                Actions\EditAction::make()
                    ->label('Edit User')
                    ->icon('heroicon-o-pencil'),
                
                Actions\DeleteAction::make()
                    ->label('Delete User')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\BulkAction::make('updateRoles')
                        ->label('Update Roles')
                        ->icon('heroicon-o-user-group')
                        ->form([
                            Select::make('role')
                                ->label('New Role Level')
                                ->options([
                                    0 => 'User',
                                    1 => 'Juror',
                                    2 => 'Host',
                                    3 => 'Moderator',
                                    4 => 'Site Admin',
                                ])
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each->update(['role' => $data['role']]);
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Roles updated successfully')
                                ->success()
                                ->send();
                        }),
                    
                    Actions\BulkAction::make('delete')
                        ->label('Delete Selected')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each->delete();
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Users deleted successfully')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
