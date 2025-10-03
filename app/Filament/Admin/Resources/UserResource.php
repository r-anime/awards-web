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

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'User Management';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->role >= 3;
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
                    ->options([
                        0 => 'User',
                        1 => 'Juror',
                        2 => 'Host',
                        3 => 'Moderator',
                        4 => 'Site Admin',
                    ])
                    ->required(),
                
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
