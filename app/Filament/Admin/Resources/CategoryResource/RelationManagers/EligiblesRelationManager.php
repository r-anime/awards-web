<?php

namespace App\Filament\Admin\Resources\CategoryResource\RelationManagers;

use App\Models\CategoryEligible;
use App\Models\Entry;
use App\Models\ItemName;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Sleep;
use App\Jobs\DownloadImage;

class EligiblesRelationManager extends RelationManager
{
    protected static string $relationship = 'eligibles';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('entry_id')
                    ->label('Entry')
                    ->required()
                    ->options(function (Get $get) {
                        $parent_cat = $this->getOwnerRecord();
                        $year = $parent_cat->year;

                        // TODO: Implement type constraints for search

                        return Entry::where('year', $year)
                            ->pluck('name', 'id');
                    })
                    ->searchable(),
                Toggle::make('active')
                    ->required()
                    ->default(true),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        Fieldset::make()
                            ->columns(1)
                            ->contained(false)
                            ->schema([
                                TextEntry::make('entry.name'),
                                TextEntry::make('entry.year'),
                                TextEntry::make('entry_id')
                                    ->numeric(),
                                IconEntry::make('active')
                                    ->boolean(),
                                TextEntry::make('created_at')
                                    ->dateTime()
                                    ->placeholder('-'),
                                TextEntry::make('updated_at')
                                    ->dateTime()
                                    ->placeholder('-'),
                            ]),
                        ImageEntry::make('entry.image')
                            ->imageSize(400)
                            ->disk('public'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('eligible')
            ->columns([
                TextColumn::make('entry_id')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('entry.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('entry.type')
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('active'),
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
                //
            ])
            ->headerActions([
                CreateAction::make(),
                Action::make('bulkAddByAnilistId')
                    ->label('Bulk Add by Anilist ID')
                    ->icon('heroicon-o-plus-circle')
                    ->form([
                        Textarea::make('anilist_ids')
                            ->label('Anilist IDs')
                            ->helperText('Enter one Anilist ID per line, or comma-separated')
                            ->required()
                            ->rows(10),
                        Toggle::make('active')
                            ->label('Set as active')
                            ->default(true),
                    ])
                    ->action(function (array $data) {
                        // Increase execution time limit for bulk operations
                        set_time_limit(300); // 5 minutes
                        
                        $category = $this->getOwnerRecord();
                        $anilistIds = $this->parseAnilistIds($data['anilist_ids']);
                        $active = $data['active'] ?? true;
                        
                        if (empty($anilistIds)) {
                            Notification::make()
                                ->title('No valid Anilist IDs found')
                                ->danger()
                                ->send();
                            return;
                        }
                        
                        // Limit batch size to prevent timeout
                        $batchSize = 20;
                        $totalIds = count($anilistIds);
                        
                        if ($totalIds > $batchSize) {
                            Notification::make()
                                ->title("Processing in batches")
                                ->body("Processing {$totalIds} IDs in batches of {$batchSize} to prevent timeout")
                                ->warning()
                                ->send();
                        }
                        
                        $successCount = 0;
                        $errorCount = 0;
                        $errors = [];
                        $processed = 0;
                        
                        foreach ($anilistIds as $index => $anilistId) {
                            try {
                                // Add delay between API requests to avoid rate limiting
                                if ($index > 0 && $index % 5 == 0) {
                                    Sleep::for(1)->second();
                                }
                                
                                // Fetch from Anilist API first to determine the entry type
                                // (anilist_id is not unique across types, so we need type to find the correct entry)
                                $entry = $this->fetchAndCreateEntryFromAnilist($anilistId, $category->year);
                                
                                if (!$entry) {
                                    $errorCount++;
                                    $errors[] = "Anilist ID {$anilistId}: Could not fetch from Anilist API";
                                    continue;
                                }
                                
                                // Check if eligible already exists
                                $existing = CategoryEligible::where('category_id', $category->id)
                                    ->where('entry_id', $entry->id)
                                    ->first();
                                
                                if ($existing) {
                                    // Update active status if needed
                                    if ($existing->active != $active) {
                                        $existing->update(['active' => $active]);
                                    }
                                    $successCount++;
                                } else {
                                    // Create new eligible
                                    CategoryEligible::create([
                                        'category_id' => $category->id,
                                        'entry_id' => $entry->id,
                                        'active' => $active,
                                    ]);
                                    $successCount++;
                                }
                                
                                $processed++;
                                
                                // Process in batches to prevent timeout
                                if ($processed >= $batchSize && $processed < $totalIds) {
                                    Notification::make()
                                        ->title("Progress: {$processed}/{$totalIds} processed")
                                        ->body("Continuing with next batch...")
                                        ->info()
                                        ->send();
                                    
                                    // Small delay between batches
                                    Sleep::for(2)->second();
                                }
                            } catch (\Exception $e) {
                                $errorCount++;
                                $errors[] = "Anilist ID {$anilistId}: " . $e->getMessage();
                            }
                        }
                        
                        $message = "Successfully added {$successCount} eligible(s)";
                        if ($errorCount > 0) {
                            $message .= " with {$errorCount} error(s)";
                        }
                        $message .= ". Images are being downloaded in the background.";
                        
                        Notification::make()
                            ->title($message)
                            ->success()
                            ->send();
                        
                        if (!empty($errors)) {
                            \Log::warning('Bulk add eligibles errors', ['errors' => $errors]);
                        }
                    }),
                // AssociateAction::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                // EditAction::make(),
                // DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Parse Anilist IDs from input string (supports newlines and commas)
     */
    private function parseAnilistIds(string $input): array
    {
        // Split by newlines and commas, then filter out empty values
        $ids = preg_split('/[\n\r,]+/', $input);
        $ids = array_map('trim', $ids);
        $ids = array_filter($ids, function ($id) {
            return !empty($id) && is_numeric($id);
        });
        
        // Convert to integers
        $ids = array_map('intval', $ids);
        
        return array_values($ids);
    }

    /**
     * Fetch entry from Anilist API and create Entry record
     */
    private function fetchAndCreateEntryFromAnilist(int $anilistId, int $year): ?Entry
    {
        $query = 'query ($id: Int) {
            Media(id: $id) {
                id
                type
                format
                startDate {
                    year
                }
                title {
                    romaji
                    english
                }
                synonyms
                coverImage {
                    large
                    extraLarge
                }
                siteUrl
                idMal
            }
        }';

        $response = Http::post('https://graphql.anilist.co', [
            'query' => $query,
            'variables' => [
                'id' => $anilistId,
            ],
        ]);

        if (!$response->ok()) {
            return null;
        }

        $data = $response->json();
        
        if (!isset($data['data']['Media'])) {
            return null;
        }

        $media = $data['data']['Media'];
        
        // Determine entry type based on Anilist media type
        $entryType = 'anime'; // Default
        if ($media['type'] === 'MANGA') {
            $entryType = 'manga';
        } elseif (isset($media['characters'])) {
            // This would be for characters, but we'll keep it simple for now
            $entryType = 'anime';
        }

        // Download image (queue to avoid blocking/timeouts)
        $imageUrl = $media['coverImage']['large'] ?? $media['coverImage']['extraLarge'] ?? null;
        $imageFilename = null;
        
        if ($imageUrl) {
            $imageFilename = '/entry/anilist-' . $anilistId . '.jpg';
            
            // Queue image download to avoid blocking and prevent timeouts
            DownloadImage::dispatch($imageFilename, $imageUrl);
        }

        // Create or update entry
        $entry = Entry::updateOrCreate(
            [
                'type' => $entryType,
                'anilist_id' => $anilistId,
            ],
            [
                'name' => $media['title']['romaji'] ?? $media['title']['english'] ?? 'Unknown',
                'year' => $media['startDate']['year'] ?? $year,
                'image' => $imageFilename,
            ]
        );

        // Create item names
        if (isset($media['title']['romaji'])) {
            ItemName::updateOrCreate(
                [
                    'entry_id' => $entry->id,
                    'language_code' => 'jp',
                ],
                [
                    'name' => $media['title']['romaji'],
                ]
            );
        }

        if (isset($media['title']['english'])) {
            ItemName::updateOrCreate(
                [
                    'entry_id' => $entry->id,
                    'language_code' => 'en',
                ],
                [
                    'name' => $media['title']['english'],
                ]
            );
        }

        // Add synonyms
        if (isset($media['synonyms']) && is_array($media['synonyms'])) {
            foreach ($media['synonyms'] as $synonym) {
                if (!empty($synonym)) {
                    ItemName::updateOrCreate(
                        [
                            'entry_id' => $entry->id,
                            'language_code' => 'alternate',
                            'name' => $synonym,
                        ],
                        []
                    );
                }
            }
        }

        return $entry;
    }
}
