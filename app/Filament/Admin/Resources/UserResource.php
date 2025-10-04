<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'User Management';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->role >= 2;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->label('Username'),
                
                TextInput::make('reddit_user')
                    ->label('Reddit Username')
                    ->required(),
                
                TextInput::make('email')
                    ->label('Email')
                    ->email(),
                
                Select::make('role')
                    ->label('Role Level')
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
                
                TextInput::make('flags')
                    ->label('Flags')
                    ->numeric(),
                
                Textarea::make('about')
                    ->label('About')
                    ->rows(3),
                
                FileUpload::make('avatar')
                    ->label('Avatar')
                    ->image()
                    ->directory('avatars'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Username')
                    ->searchable(),
                Tables\Columns\TextColumn::make('reddit_user')
                    ->label('Reddit Username')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->label('Role')
                    ->formatStateUsing(function ($state) {
                        $roles = [
                            -1 => 'Restricted',
                            0 => 'User',
                            1 => 'Juror',
                            2 => 'Host',
                            3 => 'Moderator',
                            4 => 'Site Admin',
                        ];
                        return $roles[$state] ?? 'Unknown';
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                \Filament\Actions\EditAction::make()
                    ->visible(function ($record) {
                        return $record->role <= auth()->user()->role;
                    }),
                \Filament\Actions\DeleteAction::make()
                    ->visible(function ($record) {
                        return $record->role <= auth()->user()->role;
                    }),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make()
                        ->visible(function ($records) {
                            return $records->every(function ($record) {
                                return $record->role <= auth()->user()->role;
                            });
                        }),
                ]),
            ])
            ->modifyQueryUsing(function ($query) {
                // Only show users with roles equal to or lower than current user's role
                return $query->where('role', '<=', auth()->user()->role);
            });
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
