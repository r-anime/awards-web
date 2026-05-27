<?php

namespace App\Filament\Admin\Resources\Acknowledgements\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class AcknowledgementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('title')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('subtitle')
                    ->default(' ')
                    ->columnSpanFull(),
                TextInput::make('year')
                    ->default(session('selected-year-filter') ?? app('current-year'))
                    ->required()
                    ->numeric()
                    ->maxLength(4)
                    ->reactive()
                    ->readOnly(),                 // Disable accidental misinput
                TextInput::make('order')
                    ->required()
                    ->default(0)
                    ->numeric(),
                TextArea::make('content.english')
                    ->rows(12)
                    ->columnSpanFull()
                    ->extraAttributes(['style' => 'width: 636px;'])
                    ->extraInputAttributes(['style' => 'text-align: center;'])
                    ->required(),
                TextArea::make('content.japanese')
                    ->rows(12)
                    ->columnSpanFull()
                    ->extraAttributes(['style' => 'width: 636px;'])
                    ->extraInputAttributes(['style' => 'text-align: center;']),
                TextArea::make('content.extra')
                    ->rows(12)
                    ->columnSpanFull()
                    ->extraAttributes(['style' => 'width: 636px;'])
                    ->extraInputAttributes(['style' => 'text-align: center;']),
            ]);
    }
}
