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
                                ->options(function () {
                                    $currentUserRole = auth()->user()->role;
                                    $roleOptions = [
                                        -1 => 'Restricted',
                                        0 => 'User',
                                        1 => 'Juror',
                                        2 => 'Host',
                                        3 => 'Moderator',
                                        4 => 'Site Admin',
                                    ];
                                    
                                    // Filter out roles higher than the current user's role
                                    return array_filter($roleOptions, function ($role, $key) use ($currentUserRole) {
                                        return $key <= $currentUserRole;
                                    }, ARRAY_FILTER_USE_BOTH);
                                })
                                ->required()
                                ->rules([
                                    function () {
                                        return function (string $attribute, $value, \Closure $fail) {
                                            $currentUserRole = auth()->user()->role;
                                            if ($value > $currentUserRole) {
                                                $fail('You cannot assign a role higher than your own.');
                                            }
                                        };
                                    },
                                ]),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $currentUserRole = auth()->user()->role;
                            $newRole = $data['role'];
                            
                            // Additional validation: ensure we're not trying to assign roles higher than current user's role
                            if ($newRole > $currentUserRole) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Permission denied')
                                    ->body('You cannot assign a role higher than your own.')
                                    ->danger()
                                    ->send();
                                return;
                            }
                            
                            // Only update users whose current role is equal to or lower than current user's role
                            $validRecords = $records->filter(function ($record) use ($currentUserRole) {
                                return $record->role <= $currentUserRole;
                            });
                            
                            if ($validRecords->count() !== $records->count()) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Some users skipped')
                                    ->body('You can only modify users with roles equal to or lower than your own.')
                                    ->warning()
                                    ->send();
                            }
                            
                            $validRecords->each->update(['role' => $newRole]);
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Roles updated successfully')
                                ->success()
                                ->send();
                        })
                        ->visible(function (Collection $records) {
                            // Only show this action if all selected users have roles <= current user's role
                            $currentUserRole = auth()->user()->role;
                            return $records->every(function ($record) use ($currentUserRole) {
                                return $record->role <= $currentUserRole;
                            });
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
