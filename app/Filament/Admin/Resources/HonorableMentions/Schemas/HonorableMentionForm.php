<?php

namespace App\Filament\Admin\Resources\HonorableMentions\Schemas;

use App\Models\Category;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class HonorableMentionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('year')
                    ->default(session('selected-year-filter') ?? app('current-year'))
                    ->required()
                    ->readOnly()
                    ->numeric(),
                Select::make('category_id')
                    ->label('Category')
                    ->required()
                    ->options(function () {
                        $filterYear = session('selected-year-filter') ?? intval(app('current-year'));
                        return Category::where('year', $filterYear)->pluck('name', 'id');
                    })
                    ->searchable(),
                MarkdownEditor::make('writeup')
                    ->required()
                    ->columnSpanFull()
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('descriptions'),
            ]);
    }
}
