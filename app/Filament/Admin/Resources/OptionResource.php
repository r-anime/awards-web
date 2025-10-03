<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OptionResource\Pages;
use App\Models\Option;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;

class OptionResource extends Resource
{
    protected static ?string $model = Option::class;

    protected static ?string $navigationLabel = 'Options';

    protected static ?string $modelLabel = 'Options';

    protected static ?string $pluralModelLabel = 'Options';

    protected static ?int $navigationSort = 10;

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->role >= 4;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Application Settings')
                    ->description('Configure general application settings')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('account_age_requirement')
                                    ->label('Account Age Requirement (days)')
                                    ->numeric()
                                    ->default(fn() => Option::get('account_age_requirement', 30))
                                    ->helperText('Minimum account age in days for user registration')
                                    ->required(),

                                TextInput::make('site_name')
                                    ->label('Site Name')
                                    ->default(fn() => Option::get('site_name', 'r/anime Awards'))
                                    ->helperText('The name of your application')
                                    ->required(),
                            ]),
                    ]),

                Section::make('Discord Integration')
                    ->description('Configure Discord webhook settings')
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                TextInput::make('feedback_channel_webhook')
                                    ->label('Feedback Channel Webhook URL')
                                    ->url()
                                    ->default(fn() => Option::get('feedback_channel_webhook', ''))
                                    ->helperText('Discord webhook URL for feedback channel notifications')
                                    ->placeholder('https://discord.com/api/webhooks/...'),

                                TextInput::make('audit_channel_webhook')
                                    ->label('Audit Channel Webhook URL')
                                    ->url()
                                    ->default(fn() => Option::get('audit_channel_webhook', ''))
                                    ->helperText('Discord webhook URL for audit channel notifications')
                                    ->placeholder('https://discord.com/api/webhooks/...'),
                            ]),
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
            'index' => Pages\ManageOptions::route('/'),
        ];
    }
}
